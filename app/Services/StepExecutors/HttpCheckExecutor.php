<?php

namespace App\Services\StepExecutors;

use App\Models\Step;
use App\Models\Run;
use App\Models\RunLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class HttpCheckExecutor implements StepExecutorInterface
{
    /**
     * Request timeout in seconds.
     */
    const TIMEOUT_SECONDS = 2;

    /**
     * Execute an HTTP check step.
     *
     * @param Step $step
     * @param Run $run
     * @return bool
     */
    public function execute(Step $step, Run $run): bool
    {
        $config = $step->config;

        // Validate that URL is provided
        if (!isset($config['url'])) {
            RunLog::error(
                $run,
                $step,
                "HTTP check step missing required 'url' parameter in config"
            );
            return false;
        }

        $url = $config['url'];

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            RunLog::error(
                $run,
                $step,
                "Invalid URL format: {$url}"
            );
            return false;
        }

        // Validate URL scheme (only allow http/https)
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            RunLog::error(
                $run,
                $step,
                "URL must use http or https protocol: {$url}"
            );
            return false;
        }

        // Log start of HTTP check
        RunLog::info(
            $run,
            $step,
            "Starting HTTP GET request to: {$url}"
        );

        try {
            // Make the HTTP request with timeout
            $startTime = microtime(true);

            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withoutVerifying() // For testing purposes, skip SSL verification
                ->get($url);

            $duration = round((microtime(true) - $startTime) * 1000, 2); // Convert to ms
            $statusCode = $response->status();

            // Determine success based on status code
            $isSuccessful = $response->successful(); // 2xx status codes

            if ($isSuccessful) {
                RunLog::info(
                    $run,
                    $step,
                    "HTTP request succeeded - Status: {$statusCode}, Duration: {$duration}ms"
                );
                return true;
            }

            // Client errors (4xx)
            if ($response->clientError()) {
                RunLog::warn(
                    $run,
                    $step,
                    "HTTP client error - Status: {$statusCode}, Duration: {$duration}ms"
                );
                return false;
            }

            // Server errors (5xx)
            if ($response->serverError()) {
                RunLog::error(
                    $run,
                    $step,
                    "HTTP server error - Status: {$statusCode}, Duration: {$duration}ms"
                );
                return false;
            }

            // Other status codes (1xx, 3xx redirects that weren't followed)
            RunLog::warn(
                $run,
                $step,
                "HTTP request returned non-success status: {$statusCode}, Duration: {$duration}ms"
            );
            return false;

        } catch (ConnectionException $e) {
            // Connection failed (DNS, network issues, timeout)
            RunLog::error(
                $run,
                $step,
                "Connection failed: {$e->getMessage()}"
            );
            return false;

        } catch (RequestException $e) {
            // Request exception (usually after response received)
            RunLog::error(
                $run,
                $step,
                "Request failed: {$e->getMessage()}"
            );
            return false;

        } catch (\Exception $e) {
            // Catch any other unexpected errors
            RunLog::error(
                $run,
                $step,
                "Unexpected error during HTTP check: {$e->getMessage()}"
            );
            return false;
        }
    }

    /**
     * Check if this executor supports the given step type.
     *
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        return $type === Step::TYPE_HTTP_CHECK;
    }
}
