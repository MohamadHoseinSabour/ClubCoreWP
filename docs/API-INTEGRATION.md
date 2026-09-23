# Melipayamak API Integration

## Connection Details
- **Base URL**: `https://rest.payamak-panel.com/api/SendSMS/`
- **Auth**: Username & Password (or API Token for newer Console).

## Core Endpoints
1. **BaseServiceNumber (Pattern SMS)**
   - Method: POST
   - Params: `username`, `password`, `to`, `bodyId`, `text`
   - Purpose: Sends template-based OTP/Notifications.
2. **GetCredit**
   - Method: POST
   - Params: `username`, `password`
3. **GetDeliveries**
   - Method: POST
   - Params: `username`, `password`, `recId`

## Formatting & Rules
- **Pattern Rules**: `text` parameter requires variable values to be separated by `;`. `bodyId` maps to the template ID in the panel.
- **Response Format**: JSON `{ "Value": "string", "RetStatus": int }`

## Error Handling
| Code | Meaning |
|---|---|
| 0, -1, 2, 6, 7, 10, 11, 12, 19, 35 | Various Melipayamak specific errors mapped to Domain Exceptions |

## Constraints
- **Rate limiting considerations** are implemented locally to prevent spamming.
- **Timeout** configured to avoid locking up WordPress processes.
