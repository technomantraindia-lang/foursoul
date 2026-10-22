# YouTube Live QA

The existing live-learning runtime provides scheduled sessions, server-side
enrollment/entitlement checks, configurable join timing through
`LiveSessionService`, attendance, and replay URL gating. Expired or
unauthorized users do not receive replay URLs or a successful join response.

The persisted provider set now also includes YouTube Live. The existing
`meeting_url` field stores the public watch/live URL, and the server converts
only valid YouTube IDs to an embed URL after access validation. No YouTube API
or secret is used. Replay conversion into a lesson remains a separate product
workflow because the current schema has no live-session-to-lesson reference.

Application business time now defaults to `BUSINESS_TIMEZONE`, configured as
`Asia/Dubai`, with per-user display timezone handling retained for calendars.
The default join window remains 15 minutes before start with a 30-minute grace
period after end.
