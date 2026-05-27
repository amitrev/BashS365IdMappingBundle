## 2025-05-14 - [Memoization & Merge Optimization]
**Learning:** Class-level `readonly` prevents in-memory memoization. Switching to property-level `readonly` (PHP 8.2) allows adding private non-readonly properties for caching without sacrificing external immutability. Also, `array_merge_recursive` for HttpClient options can be dangerous and slow.
**Action:** Use property-level `readonly` for DTOs/Services that need internal state for performance. Prefer spread operator or manual merging for HttpClient options to ensure correct header overrides.

## 2025-05-14 - [End-to-end Streaming]
**Learning:** Returning a large string from `S365Response::getContent()` and then echoing it in a `StreamedResponse` still buffers the entire body in memory.
**Action:** Implement `S365Response::toIterable()` to yield chunks directly from `HttpClient::stream()` to the final output, ensuring constant memory usage even for massive payloads.

## 2025-05-14 - [Header Normalization & Pre-calculation]
**Learning:** Symfony HttpClient (and HTTP in general) treats headers as case-insensitive. Normalizing them to lowercase early avoids redundant internal normalization. Pre-calculating full option arrays in the constructor reduces overhead during high-frequency requests.
**Action:** Always lowercase internal header keys and pre-calculate base configuration arrays when possible.

## 2025-05-14 - [Avoiding Redundant Body Reads]
**Learning:** Calling `$request->getContent()` or `$request->getContent(true)` on GET/HEAD requests is unnecessary and can be wasteful.
**Action:** Only read the request body for methods that typically contain one (POST, PUT, PATCH, etc.).

## 2025-05-14 - [Efficient Header Filtering]
**Learning:** Manually unsetting multiple header keys is slower than `array_diff_key`.
**Action:** Use `array_diff_key` with a static map of keys to remove.
