# Voucher Changes Overview (January 2026)

This note summarizes the recent voucher-related updates so other developers can trace what changed and why.

## 1. Shared Voucher Service
- **File:** `includes/vouchers/service.php`
- Added `source` and `student_id` to the `previewVoucher()` payload so downstream callers know where a code originated and who it belongs to.
- `markRedeemed()` still enforces expiry/usage logic but now returns enough detail for checkout to notify collaborators immediately after a successful redemption.

## 2. Preview API Hardening
- **File:** `api/vouchers/preview.php`
- Response now exposes only `code`, `discount_type`, and `discount_value` to avoid leaking metadata (e.g., student IDs or internal state) to public callers.
- Clients still receive HTTP 404/409 errors for missing or invalid vouchers.

## 3. Checkout Redemption Flow
- **File:** `pages/checkout.php`
- Voucher apply button uses `Vouchers\previewVoucher()` + `computeDiscount()` to recalculate totals before placing an order.
- After the order is inserted, checkout calls `markRedeemed()` with either the voucher’s `student_id` (course-issued codes) or the logged-in customer ID (SMS codes).
- When the voucher’s `source` is `api`, checkout also calls `notifyCollaborator()` so partners receive the same webhook payload they expect from the REST endpoint.

## 4. Documentation Update
- **File:** `docs/voucher-api.md`
- Clarified that redemptions initiated on `https://dev.art2cart.shop/` send collaborator webhooks—students checking out on the storefront still trigger partner systems.

## 5. Database Migration
- **File:** `sql/migrations/2026-01-16-add-voucher-discounts.sql`
- Migration adds `discount_type` and `discount_value` columns (with safe defaults) so both SMS and course-issued vouchers can define either percent or fixed discounts per code.
- Run this migration on any environment before testing discount logic.

---
**Next steps for teammates**
1. Apply the latest migration to your local/remote database.
2. Generate a test voucher (both SMS and course/API channels) and redeem it via checkout to confirm totals + collaborator webhook. You can issue a one-off course voucher with:

	```bash
	curl -X POST https://dev.art2cart.shop/api/vouchers/generate.php \
		-H "Authorization: Bearer $COURSE_API_KEY" \
		-H "Content-Type: application/json" \
		-d '{"student-id":"sandbox-123"}'
	```
	> ⚠️ On Windows `cmd.exe`, nested quotes can cause auth failures. Either run the command in Git Bash/WSL or use PowerShell’s `Invoke-RestMethod` instead:

	```powershell
	$headers = @{
	    Authorization = 'Bearer YOUR_KEY'
	    'Content-Type' = 'application/json'
	}
	Invoke-RestMethod -Uri 'https://dev.art2cart.shop/api/vouchers/generate.php' \`
	    -Headers $headers -Method Post -Body '{"student-id":"course-42"}'
	```
3. Update any API clients to handle the trimmed `/vouchers/preview` response shape.
