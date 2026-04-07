# S365 Content API Bundle

- PHP 8.2+
- Symfony 7.4

This bundle provides a robust, DDD-oriented integration for the S365 Id Mapping API. It supports both direct API interaction and transparent request forwarding (proxying).

## Features

- **DDD Architecture**: Separated Domain and Infrastructure layers.
- **Auto-Authentication**: Handles OAuth2 Password Grant flow with internal caching.
- **Request Proxying**: Easily forward requests to the S365 API via a dedicated controller.
- **Traceability**: Seamless integration with Correlation IDs for end-to-end logging.
- **Typed Responses**: Returns structured data objects instead of raw arrays.

## Installation

### 1. Add Environment Variables
Define the following variables in your `.env` or server environment:

```yaml
###> S365 ID MAPPING API ###

S365_ID_MAPPING_API_URL=https://example.com
S365_ID_MAPPING_API_USERNAME=your_username
S365_ID_MAPPING_API_PASSWORD=your_password
S365_ID_MAPPING_API_PROJECT=your_project_code

###< S365 ID MAPPING API ###
```

### 2. Manual Configuration

Since this bundle does not use a public Flex recipe, you must create the configuration files manually. You can find templates in the `recipe/` directory of this bundle.

#### A. Create Service Configuration (Required)

Copy the `recipe/config/packages/s365_id_mapping.yaml` file to your project's `config/packages/s365_id_mapping.yaml`.

#### B. Create Routing Configuration (Optional)

If you want to use the proxy controller, copy the `recipe/config/routes/s365_id_mapping.yaml` file to your project's `config/routes/s365_id_mapping.yaml` and adjust the prefix if needed.

### 3. Install the Package

Simply run:
```bash
composer require bash/s365-id-mapping-bundle
```

## Usage

### Direct API Calls

The `IdMappingClient` is automatically wired and ready to use. It handles authentication and headers internally.

```php
namespace App\Controller;

use Bash\S365IDMappingBundle\Infrastructure\HttpClient\IdMappingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends AbstractController
{
    public function __construct(
        private readonly IdMappingClient $idMappingClient
    ) {}

    public function list(): Response
    {
        // No try-catch needed if you have a global ExceptionListener
        $response = $this->idMappingClient->request('GET', 'articles?limit=10');
        
        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            ['Content-Type' => 'application/json']
        );
    }
}
```

### Using Correlation IDs for Traceability

To maintain end-to-end traceability, you can pass your application's Correlation ID to the client. This ID will be sent to S365 via the `X-Correlation-ID` header.
```php

// Inside your service or controller
$correlationId = $request->headers->get('X-Correlation-ID');

$response = $this->idMappingClient->forward(
    method: 'GET',
    url: 'articles',
    correlationId: $correlationId // The ID is now linked to S365 logs
);
```

### Transparent Request Forwarding (Proxy)
Once the routes are registered, you can make requests directly to your own API domain. The bundle will authenticate and forward them to S365 automatically:

**Example Flow:**
1. Client calls: `GET https://your-api.com/s365id/proxy/articles?limit=10`
2. Bundle forwards to: `GET https://s365-id-mapping-api.com/articles?limit=10`
3. Client receives: Raw JSON response from S365.

*Note: The proxy controller automatically strips sensitive headers like `Authorization` and `Content-Encoding` to ensure security and compatibility.*

## Error Handling

All exceptions thrown by this bundle implement `S365IDMappingExceptionInterface`. This allows you to catch all S365-related errors centrally in your `ExceptionListener` or `Subscriber`.

| Exception                    | Description                                                     |
|------------------------------|-----------------------------------------------------------------|
| `S365CommunicationException` | Network or transport errors (timeouts, DNS issues).             |
| `S365IDMappingException`     | The API returned a malformed response or a non-2xx status code. |

Example of catching errors in your application:

```php
try {
    $data = $this->idMappingClient->request('GET', 'endpoint')->toArray();
} catch (S365IDMappingExceptionInterface $e) {
    // Log once, handle globally
    throw $e; 
}
```

## Logging & Monitoring

This bundle logs to the `s365_id_mapping` Monolog channel. To catch these logs in your main application, add the channel to your configuration:

```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        external_api:
            type: rotating_file
            path: "%kernel.logs_dir%/external_api.log"
            channels: ["s365_id_mapping"]
```

## Development & Testing

### Quality Control

Run static analysis and code style fixing:

```bash
composer phpstan
composer cs-fix
composer test
```

## License

MIT