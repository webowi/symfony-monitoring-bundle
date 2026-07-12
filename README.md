# Symfony Monitoring Bundle

Ships error-level Symfony logs (with full surrounding context) to [Monitoring Webowi](https://github.com/webowi/monitoring-webowi) with near-zero configuration.

## Install

```bash
composer require webowi/symfony-monitoring-bundle
```

Add to `config/packages/symfony_monitoring.yaml`:

```yaml
symfony_monitoring:
    url: '%env(MONITORING_WEBOWI_URL)%'
    api_key: '%env(MONITORING_WEBOWI_API_KEY)%'
    level: error
```

Get your project's `url` and `api_key` from your Monitoring Webowi project's ingestion-key page.

That's it — no `monolog.yaml` wiring needed. The bundle registers the full handler chain (including a `fingers_crossed` buffer so the full DEBUG-and-up context leading up to an error is sent, not just the error line itself) automatically.

## Configuration reference

| Key | Required | Default | Description |
| --- | --- | --- | --- |
| `url` | yes | — | Base URL of your Monitoring Webowi instance |
| `api_key` | yes | — | Your project's ingestion API key |
| `level` | no | `error` | PSR-3 level at which a buffered request's context is flushed and sent |

## License

MIT
