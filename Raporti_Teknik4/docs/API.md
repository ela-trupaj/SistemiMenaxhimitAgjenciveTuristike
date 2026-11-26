# API JSON — Raporti Teknik 2

API përdor **session cookie** të administratorit dhe për metodat që ndryshojnë të dhëna header-in `X-CSRF-Token`. Ky token merret nga `csrf()` në një faqe PHP të autentikuar. API nuk përdor bearer token. Për testim në Postman, importoni cookie-n e sesionit të browser-it dhe përdorni token-in CSRF të atij sesioni. Mos e ndani token-in me persona të tjerë.

| Metoda | Endpoint | Përgjigja |
|---|---|---|
| GET | `/api/index.php?entity=ofertat` | 200, listë JSON |
| GET | `/api/index.php?entity=rezervimet&id=1` | 200 ose 404 |
| POST | `/api/index.php?entity=destinacionet` | 201 ose 422 |
| PATCH | `/api/index.php?entity=rezervimet&id=1` | 200, 404 ose 422 |
| DELETE | `/api/index.php?entity=ofertat&id=1` | 200, 404 ose 409 |

Entitetet e lejuara: `destinacionet`, `ofertat`, `rezervimet`, `pagesat`.

Shembull POST (JSON):
```json
{"emri":"Prizren","shteti":"Kosovë","pershkrimi":"Destinacion kulturor"}
```

Shembull PATCH për rezervim (duhen të gjitha fushat e formularit):
```json
{"perdoruesi_id":"1","oferta_id":"1","numri_personave":"2","statusi":"Konfirmuar"}
```

Gabime: 401 (pa login), 403 (pa rol admin), 404 (entitet/ID i panjohur), 419 (CSRF), 422 (validim), 409 (rekord i lidhur). Kjo është API e demonstrimit të fazës së backend-it, jo një API publike.
