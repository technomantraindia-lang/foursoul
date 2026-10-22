# FourSouls System Infographic

```mermaid
flowchart LR
 A[ADMIN] --> B[COURSE]
 A --> C[PLAN]
 B --> D[SECTIONS]
 D --> E[LESSONS]
 C --> F[SUBSCRIPTION]
 F --> G[ENTITLEMENT]
 G --> H[ENROLLMENT]
 H --> I[LEARNING]
 I --> J[PROGRESS]
 I --> K[ASSESSMENT]
 K --> L[CERTIFICATE]
 I --> M[LIVE SESSION / REPLAY]
 N[CUSTOMER] --> C
 N --> B
 O[PAYMENT] -. provider pending .-> F
```

Enrollment is the course relationship. Entitlement is the current permission for protected access. A customer may retain enrollment history while an expired entitlement blocks paid lessons.
