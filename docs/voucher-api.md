# SoleSource Voucher API

Base URL (Cloudflare tunnel): `https://dev.art2cart.shop/api`
Authentication: `Authorization: Bearer <COURSE_API_KEY>`
Content-Type: `application/json`
All payload keys use kebab-case.

## Quick Start
1. Ask SoleSource for the shared `COURSE_API_KEY` and set it as a Bearer token header.
2. Issue a voucher (course completion):
  ```bash
  curl -X POST https://dev.art2cart.shop/api/vouchers/generate.php \
      -H "Authorization: Bearer <COURSE_API_KEY>" \
      -H "Content-Type: application/json" \
      -d '{"student-id":"course-42"}'
  ```
3. Redeem during checkout:
  ```bash
  curl -X POST https://dev.art2cart.shop/api/vouchers/redeem.php \
      -H "Authorization: Bearer <COURSE_API_KEY>" \
      -H "Content-Type: application/json" \
      -d '{"voucher-code":"REWARD-1A2B","student-id":"course-42","order-number":"ORDER-9001"}'
  ```
4. Confirm you receive a `200 OK` and that your webhook endpoint logs the payload shown below.

## POST /vouchers/generate
Creates a voucher for a student or triggers SMS issuance.

### Request Body
| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `student-id` | string | yes | LMS identifier |
| `usage-limit` | integer | optional | Defaults to 1 |
| `channel` | `api` or `sms` | optional | Defaults to `api` |
| `phone-number` | string | required when `channel`=`sms` | E.164 format |
| `discount-type` | `percent` or `fixed` | optional | Defaults: `percent` |
| `discount-value` | number | optional | Defaults: 10% for API, 5% for SMS |

### Responses
| Status | Body |
| --- | --- |
| `201 Created` | `{ "ok": true, "code": "REWARD-1A2B", "expires-at": "2026-01-20 12:00:00", "usage-limit": 1, "discount-type": "percent", "discount-value": 10 }` |
| `400 Bad Request` | `{ "ok": false, "error": "student-id-required" }` or other validation errors |
| `401 Unauthorized` | `{ "ok": false, "error": "missing-bearer-token" }` |
| `405 Method Not Allowed` | `{ "ok": false, "error": "method-not-allowed" }` |

## POST /vouchers/redeem
Redeems a voucher during checkout and notifies SoleSource + collaborator backend.

### Request Body
| Field | Type | Required |
| --- | --- | --- |
| `voucher-code` | string | yes |
| `student-id` | string | yes |
| `order-number` | string | yes |

### Responses
| Status | Body |
| --- | --- |
| `200 OK` | `{ "ok": true, "status": "redeemed", "remaining-uses": 0, "can-reuse": false, "discount-applied": 450 }` |
| `404 Not Found` | `{ "ok": false, "error": "voucher-not-found" }` |
| `409 Conflict` | `{ "ok": false, "error": "voucher-limit-hit" }` (or `voucher-expired`, etc.) |
| `401 Unauthorized` | `{ "ok": false, "error": "invalid-api-key" }` |

### Webhook Notification
After a successful redemption, SoleSource POSTs to `COLLAB_WEBHOOK_URL` with:
```json
{
  "code": "REWARD-1A2B",
  "student-id": "course-42",
  "order-number": "ORDER-9001",
  "redeemed-at": "2026-01-15T06:21:00Z",
  "remaining-uses": 0,
  "can-reuse": false,
  "discount-applied": 450,
  "integration": "course"
}
```
Respond with HTTP 200 to acknowledge. Non-200 responses are logged for manual retries.

> Note: When a student redeems the voucher directly on https://dev.art2cart.shop/, the storefront triggers the same webhook payload so you still receive the completion signal.

## SMS BOOST Flow
* Students text `BOOST` to the SoleSource number.
* `/api/sms-handler.php` generates a `SOLE-####` voucher and sends the message: "Your SoleSource code is ####. Enjoy 5% off authentic pairs—keep this message safe."
* SMS and REST flows share the same `vouchers` table (single source of truth).

## POST /vouchers/preview
Checks if a code is valid and returns the configured discount (useful before showing a coupon field on the course site).

### Request Body
| Field | Type | Required |
| --- | --- | --- |
| `voucher-code` | string | yes |

### Responses
| Status | Body |
| --- | --- |
| `200 OK` | `{ "ok": true, "voucher": { "code": "REWARD-1A2B", "discount_type": "percent", "discount_value": 10 } }` |
| `404 Not Found` | `{ "ok": false, "error": "voucher-not-found" }` |
| `409 Conflict` | `{ "ok": false, "error": "voucher-expired" }` |
