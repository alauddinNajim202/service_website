# Implementing Chat Tips Feature

This plan outlines the backend changes required to allow users to send tips to creators directly from the chat interface, using Stripe for payment processing.

## User Review Required

> [!IMPORTANT]
> **Database Changes**: We will add two new columns to the `chats` table (`message_type` and `metadata`).
> **Frontend Integration**: The frontend will need to handle the new `message_type` (`tip`) and parse the `metadata` to display the custom UI for a tip message.

## Proposed Changes

### Database

#### [NEW] [Migration: Add message_type and metadata to chats](file:///c:/laragon/www/ines_backend_website/database/migrations/)
- Create a new migration `php artisan make:migration add_type_and_metadata_to_chats_table`.
- Add `message_type` column (enum or string, default: 'text').
- Add `metadata` column (json, nullable) to store tip amount, currency, and any optional message.

### Backend logic

#### [MODIFY] [Chat.php](file:///c:/laragon/www/ines_backend_website/app/Models/Chat.php)
- Add `message_type` and `metadata` to the `$fillable` array.
- Add `metadata` to `$casts` as `array`.

#### [NEW] [TipController.php](file:///c:/laragon/www/ines_backend_website/app/Http/Controllers/Api/V1/TipController.php)
- **`checkout(Request $request)`**: Validate `creator_id`, `amount`, and optional `message`. Create a Stripe Checkout Session for the tip amount. Set the `transfer_data` destination to the creator's connected Stripe account. Return the Stripe session URL.
- **`success(Request $request)`**: Retrieve the Stripe session. If paid, create a record in the `transactions` table. Then, create a new `Chat` message with `message_type = 'tip'`, and `metadata = ['amount' => $amount, 'message' => $tip_message]`. Trigger the `MessageSendEvent` so it appears in real-time. Redirect to the frontend success URL.
- **`cancel(Request $request)`**: Redirect to the frontend fail URL.

#### [MODIFY] [api.php](file:///c:/laragon/www/ines_backend_website/routes/api.php)
- Add route: `POST /auth/chat/tip/checkout` -> `TipController@checkout`
- Add route: `GET /chat/tip/payment/success` -> `TipController@success`
- Add route: `GET /chat/tip/payment/cancel` -> `TipController@cancel`

## Verification Plan

### Automated Tests
- Test Tip Checkout endpoint validation.
- (If applicable) Mock Stripe and test the tip success logic to ensure the transaction and chat message are created.

### Manual Verification
1. Login as a User, open a chat with a Creator.
2. Hit the new `/auth/chat/tip/checkout` endpoint via Postman or the frontend to get the Stripe Checkout URL.
3. Complete the Stripe payment (using test cards).
4. Verify you are redirected to the success URL.
5. Check the `transactions` table to ensure the tip was recorded.
6. Check the `chats` table to ensure a new message of type 'tip' was created.
7. Verify the frontend receives the `MessageSendEvent` via Laravel Reverb/Pusher and displays the tip.
