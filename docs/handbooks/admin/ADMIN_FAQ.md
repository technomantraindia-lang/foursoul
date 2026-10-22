# Admin FAQ

| Question | Answer |
|---|---|
| How do I create a course? | Create it under Courses, save its details, then add sections and lessons. |
| How do I publish a course? | Complete content and pricing, then set the course to its published state. |
| Why cannot a customer see it? | Check published status, publication date, and purchase/access configuration. |
| How do I add lessons? | Open a course section and add lessons in the desired position. |
| How does enrollment work? | It records the customer's course relationship and is created by the verified fulfillment flow. |
| What is entitlement? | It is the current permission to access protected course content. |
| What happens on expiry? | Entitlements expire and paid enrollments lock; history and progress remain. |
| How does renewal work? | The customer renewal action calls `SubscriptionService::renew()`. |
| Does renewal duplicate enrollment? | No. Existing enrollment rows are updated/unlocked. |
| Is progress retained? | Yes. Renewal does not delete or reset progress. |
| How do I create a live session? | Create it with a course, timing, instructor, provider, and access rules. |
| How do payments work currently? | Offline/manual gateways exist; no UAE live provider is configured. |
