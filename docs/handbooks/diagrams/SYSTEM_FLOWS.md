# FourSouls Flow Diagrams

## 1. LMS
```mermaid
flowchart TD
 A[Admin setup] --> B[Published course]
 B --> C[Customer access]
 C --> D[Lessons and progress]
 D --> E[Assessment]
 E --> F[Certificate]
```

## 2. Course creation
```mermaid
flowchart TD
 A[Create course] --> B[Add sections] --> C[Add lessons] --> D[Set prices] --> E[Publish] --> F[Catalogue]
```

## 3. Subscription
```mermaid
flowchart TD
 A[Plan] --> B[Order] --> C[Payment confirmation] --> D[Subscription] --> E[Entitlement] --> F[Enrollment]
```

## 4. Enrollment and entitlement
```mermaid
flowchart TD
 A[Customer] --> B[Enrollment relationship] --> C[Entitlement permission] --> D[Protected course access]
```

## 5. Learning
```mermaid
flowchart TD
 A[My Learning] --> B[Open lesson] --> C[Watch video] --> D[Save progress] --> E[Complete lesson] --> F[Next lesson]
```

## 6. Expiry and renewal
```mermaid
flowchart TD
 A[Active] --> B[Expiry date passes] --> C[Paid content locked] --> D[Progress preserved] --> E[Renew] --> F[SubscriptionService renew] --> G[Access restored]
```

## 7. Live session
```mermaid
flowchart TD
 A[Admin schedules] --> B[Customer sees session] --> C[Join window] --> D[Access check] --> E[Live player] --> F[Replay]
```

## 8. Certificate
```mermaid
flowchart TD
 A[Complete requirements] --> B[Eligibility check] --> C[Issue certificate] --> D[Customer view] --> E[Public verification]
```

## 9. Payment
```mermaid
flowchart TD
 A[Customer checkout] --> B[Order frozen] --> C[Gateway] --> D[Verified callback/webhook] --> E[PaymentService] --> F[Fulfillment]
```

## 10. Troubleshooting
```mermaid
flowchart TD
 A[Cannot access lesson] --> B{Logged in?}
 B -- No --> C[Login]
 B -- Yes --> D{Course published?}
 D -- No --> E[Admin checks publication]
 D -- Yes --> F{Entitlement active?}
 F -- No --> G[Check expiry or renewal]
 F -- Yes --> H[Check enrollment and lesson release]
```
