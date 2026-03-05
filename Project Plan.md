# Base ideas

- Web page - for clients
	- Landing page with offers for creating your own account
	- Costumer support (with chatbot)
	- Login with mobile app (scan QR code, sms verification)
	- Check account balance
	- Balance chart (shows expenses and income)
	- Separate page for previously generated documents like contracts or statements
	- Loans
		- Get loans, idk how it works
		- Balance can go into negative
		- So if the account balance is 0, the client can still purchase until a limit is reached with a penalty price (limit can be set in settings)
		- Upon account closure, the client is required to pay back the loan
	- Transactions
		- Check previous transactions (within an interval)
		- Create new transactions (send virtual money to another bank account)
			- Send to email address, phone number or bank account number
		- Pay cheques
		- Regular transactions (monthly, weekly)
		- Transaction requests (create, get)
			- Create or get a request so a client can require money from another
		- Transaction status
			- Show currently active transactions (complete, pending, requires signature)
	- Account settings
		- Suspend or delete account
		- Change limit and phone number
		- Change loan limit
		- Check if clients wants email notifications
		- Change less important personal data
			- Important personal data should only be changed with a bank employee, such as name, address and maybe email address, phone number
	- Print account statement or send it in email
	- Online notifications with offers and maintenance
	- Change language
	- Change theme
- Desktop application - for bank employees
	- Create bank accounts
		- Junior account (cheaper fees)
		- Standard account
		- Premium account (pay monthly for cheaper fees)
	- Register clients
	- Change clients' personal data
	- All of the above should create a digital contract which will be signed digitally with the mobile app
	- Change language
- Mobile application - for clients and bank employees
	- For clients
		- Requires login with given codes first, after then clients can log in with biometrics or PIN
		- 2FA QR code sign in verification for website
		- Login and access the bank account just like on the website
		- Change language
	- For bank employees
		- Digitally sign documents (contracts, personal data change confirmation, etc.)
		- Change language

# Database 1: bank_core

- Contains money, balances and financial relationships with minimal access
- Only SELECT, INSERT, very limited UPDATE
- No DELETE
- Money transfer is in transactions, no credentials stored there
#### Clients

| id  | first_name | last_name | phone        | email            | address            | created_at |
| --- | ---------- | --------- | ------------ | ---------------- | ------------------ | ---------- |
| 1   | John       | Doe       | +36301234567 | johndoe@john.doe | City Smth utca 15. |            |
| 2   | Jane       | Doe       | +36709876543 | janedoe@jane.doe | halo               |            |


#### Accounts

| id  | client_id | iban | balance | overdraft_limit | status | created_at |
| --- | --------- | ---- | ------- | --------------- | ------ | ---------- |
| 1   | 2         |      | 1000    | 1000            |        |            |
| 2   | 1         |      | 50000   | 1               |        |            |
| 3   | 1         |      | 500     | 20000           |        |            |

#### Transactions

| id  | from_account_id | to_account_id | amount | status   | created_at | description |
| --- | --------------- | ------------- | ------ | -------- | ---------- | ----------- |
| 1   | 2               | 1             | 1000   | complete |            |             |
| 2   | 1               | 2             | 5000   | pending  |            |             |
| 3   | 1               | 2             | 7000   | signReq  |            |             |

#### Loans

| id  | account_id | total_amount | remaining_amount | installment_amount | interest_rate | start_date | end_date | status |
| --- | ---------- | ------------ | ---------------- | ------------------ | ------------- | ---------- | -------- | ------ |
|     |            |              |                  |                    |               |            |          |        |

#### LoanPayments

| id  | loan_id | amount | payment_date | transaction_id |
| --- | ------- | ------ | ------------ | -------------- |
|     |         |        |              |                |

# Database 2: bank_auth

- No financial data
- Strong hashing (bcrypt, argon2)

#### Users

| id  | username | password_hash | role | is_active | created_at |
| --- | -------- | ------------- | ---- | --------- | ---------- |
|     |          |               |      |           |            |

#### UserSessions

| id  | user_id | refresh_token_hash | expires_at | created_at |
| --- | ------- | ------------------ | ---------- | ---------- |
|     |         |                    |            |            |

#### MFASecrets

| id  | user_id | secret | enabled |
| --- | ------- | ------ | ------- |
|     |         |        |         |

#### UserClientLink

| user_id | client_id |
| ------- | --------- |
|         |           |

# Database 3: bank_notifications

- Separate database for notifications
- This database is not that important, if something goes wrong its not necessary for running the bank
- No authentication and financial data

#### Notifications
- type -> information, maintenence
- target_role -> everyone, client, employee

| id  | title | message | type | target_role | created_at | created_by |
| --- | ----- | ------- | ---- | ----------- | ---------- | ---------- |
|     |       |         |      |             |            |            |

#### UserNotifications

| id  | user_id | notification_id | is_read | read_at | delivered_at |
| --- | ------- | --------------- | ------- | ------- | ------------ |
|     |         |                 |         |         |              |

# Database 4: bank_audit

- INSERT only
- No UPDATE or DELETE
- Automatically store everything, this is the history of the bank


#### LedgerEntries

| id  | transaction_id | account_id | debit | credit | balance_after | created_at |
| --- | -------------- | ---------- | ----- | ------ | ------------- | ---------- |
|     |                |            |       |        |               |            |

#### SecurityEvents

| id  | event_type | user_id | ip_address | timestamp | metadata |
| --- | ---------- | ------- | ---------- | --------- | -------- |
|     |            |         |            |           |          |

# Transfer example

1. User clicks Trasfer
2. Validate input in application
3. bank_auth verify user and permissions
4. Check balance, insert Transaction and update balance
5. Insert AuditLog and LedgerEntries

- Credentials isolated  
- Financial data isolated  
- Evidence immutable  
- Least-privilege everywhere