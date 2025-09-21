# SkyBug Changelog

## 1.4.0 (2025-09-19)
- Metrics endepunkt utvidet: details/fresh param, by_type, top_programs, ttl felt
- Konfigurerbar cache TTL (`skybug_metrics_cache_ttl`) og topp-liste limit (`skybug_metrics_top_programs_limit`)
- Admin flush-knapp for metrics cache (Diverse side)
- WP-CLI kommandoer: `wp skybug metrics [--details] [--fresh]`, `wp skybug flush-metrics`
- Manual utvidet med avansert metrics dokumentasjon
- Versjonsbump / vedlikehold

## 1.3.0 (2025-09-19)
- Nytt endepunkt: GET /wp-json/skybug/v1/metrics (60s cache) gir summerte tall (program_total, issue_total, issue_open, issue_closed, siste IDer)
- Webhook test-knapp på program redigeringsside (sender event test_webhook)
- Ekstra webhook-headere: X-SkyBug-Event, X-SkyBug-Timestamp
- Manual utvidet med metrics seksjon
- Versjonsbump / vedlikehold

## 1.2.0 (2025-09-19)
- Webhook signering: X-SkyBug-Signature (HMAC SHA256) per program
- Webhook hemmelig nøkkel auto-generering
- Loggrotasjon for api_calls.log.jsonl ved >1MB
- Admin visning av siste 200 API logglinjer (undermeny API-logger)
- Manual utvidet (webhook signering, loggvisning/rotasjon)

## 1.1.0 (2025-09-19)
- Observability: correlation_id i alle REST responser
- Unified REST respons-schema (success/error)
- Rate limiting (default 60/10min per API-nøkkel) med kode rate_limited
- JSONL logging av API-kall `AI-learned/api_calls.log.jsonl`
- Forbedret REST callback med single-return mønster
- Utvidet brukermanual (korrelasjons-ID, rate limiting)

## 1.0.0 (2025-09-19)
- Første stabile utgivelse
- CPT: `skybug_program`, `skybug_issue`, taksonomi `skybug_type`
- REST endepunkt: POST /wp-json/skybug/v1/report
- Status workflow med custom status `skybug_closed`
- Webhook dispatch ved lukking av sak
- Epostvarsling for nye saker
- Program API-nøkkel generering + enable/disable
- Issue->program tilknytning meta-boks
- Shortcode `[skybug-dashboard]` for frontend oversikt
- Statistikk-side med lokal minimal graf (ingen ekstern avhengighet)
- Guardrails: automatisk skann for forbudte mønstre, syntaks sjekk
- QA aggregeringsscript `tools_run_qa.php`
- I18n: POT-fil generert (`languages/skybug.pot`)
- AI-learned kunnskaps- og verifikasjonsfiler per fase
