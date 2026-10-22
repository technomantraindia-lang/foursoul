# FourSouls frontend

Premium React/Vite client experience for the FourSouls Laravel backend.

## Local development

From this folder:

```bash
npm install
npm run dev
```

Open [http://127.0.0.1:5173](http://127.0.0.1:5173). The Vite proxy sends `/api/*` requests to Laravel at `http://127.0.0.1:8000`.

## Pages

- `/` — landing page
- `/courses` — live course catalog
- `/courses/{slug}` — course detail
- `/plans` — membership plans
- `/blog` — journal
- `/blog/{slug}` — journal detail
- `/about` — FourSouls ethos

The frontend consumes the Laravel endpoints `/api/home`, `/api/courses`, `/api/plans`, and `/api/blog`, with local fallback content for offline development.
