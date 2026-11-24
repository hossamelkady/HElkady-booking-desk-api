# API examples

Base URL: `http://127.0.0.1:8000/api`

## Create sample record

```http
POST /bookings
Accept: application/json
Content-Type: application/json

{
  "customer_name": "Samira Hassan",
  "service": "Consultation",
  "starts_at": "2026-12-02T10:00:00Z",
  "ends_at": "2026-12-02T10:45:00Z",
  "status": "confirmed"
}
```

## Validation

Submit an empty object and the API responds with HTTP 422 and field-level validation errors.
