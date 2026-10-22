# Screenshot Plan

Each item should be captured from the real local or approved staging application. Do not fabricate screenshots.

| ID | Route | What to show |
|---|---|---|
| ADM-001 | `/admin/login` | Admin login and validation |
| ADM-002 | `/admin` | Dashboard navigation and metrics |
| ADM-003 | `/admin/courses` | Course list and statuses |
| ADM-004 | `/admin/courses/create` | Course fields and pricing |
| ADM-005 | `/admin/courses/{id}/edit` | Course editing |
| ADM-006 | `/admin/subscription-plans` | Plan list/status |
| ADM-007 | `/admin/analytics` | Operational reporting |
| CUS-001 | `/register` | Registration |
| CUS-002 | `/login` | Login |
| CUS-003 | `/dashboard` | Customer dashboard |
| CUS-004 | `/courses` | Catalogue cards and empty state |
| CUS-005 | `/plans` | Plan cards and currency |
| CUS-006 | `/account/subscription` | Status, dates, and access |
| CUS-007 | `/learn` | My Learning and progress |
| CUS-008 | `/learn/courses/{slug}` | Course detail |
| CUS-009 | `/learn/courses/{slug}/lessons/{uuid}` | Lesson player |
| CUS-010 | `/learn/live-sessions` | Live sessions |
| CUS-011 | `/certificates` | Certificate list |

Annotate status badges, primary CTA, currency, access state, and any provider-blocked payment state. Required viewport set: 375, 430, 768, 1024, 1280, and 1440 pixels.

## Image placeholders

Actual screenshots are not included in this documentation pass. Capture and
replace these placeholders during final visual QA:

![CUS-004 - Course Catalogue](../images/customer/CUS-004-course-catalogue.png)

Image Purpose: Shows published course cards, prices, access state, and CTA.

![ADM-004 - Create Course](../images/admin/ADM-004-create-course.png)

Image Purpose: Shows the admin course fields, publication state, and regional pricing.
