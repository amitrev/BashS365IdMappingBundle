## 2025-05-14 - [Memoization & Merge Optimization]
**Learning:** Class-level `readonly` prevents in-memory memoization. Switching to property-level `readonly` (PHP 8.2) allows adding private non-readonly properties for caching without sacrificing external immutability. Also, `array_merge_recursive` for HttpClient options can be dangerous and slow.
**Action:** Use property-level `readonly` for DTOs/Services that need internal state for performance. Prefer spread operator or manual merging for HttpClient options to ensure correct header overrides.

## 2025-05-14 - [Streaming Request Bodies]
**Learning:** For proxy controllers, using `$request->getContent()` loads the entire request body into memory. For large requests, this can lead to memory exhaustion.
**Action:** Use `$request->getContent(true)` to obtain a resource (PHP stream) and pass it directly to the HttpClient. This enables streaming and reduces the memory peak.
