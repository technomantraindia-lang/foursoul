# Test Center QA

The Test Center remains opt-in through `TEST_CENTER_ENABLED=true`; SaaS remains
disabled. Its page delegates data creation to `TestDataService` and checks to
`SystemTestService`, so the UI does not contain domain setup logic.

The service path is deterministic and repeatable through the existing local
demo seeder, with a third deterministic test student added by
`TestDataService`. Runtime execution against the dedicated SQLite database
produced 3 students, 1 course, 1 enrollment, 1 quiz, and 1 CMS post. Running
creation a second time produced the same counts, and all 8 current system
checks passed. Reset/recreate cleanup still requires completing the isolated
control-record proof before it can be reported as PASS. The isolated reset was
executed and cleared the deterministic fixture; recreate then succeeded after
restoring known soft-deleted identifiers. Two consecutive full runs returned
8/8 passing checks. Shared MySQL was never selected by the runner.

Isolated database: `storage/app/testing/test-center.sqlite`.
Connection: `test_center`.

No payment gateway, mail, WhatsApp, push, webhook, or external queue call is
made by the health checks.
