import React, { useState, useEffect } from 'react';
import { AppLink } from './HomeBanner';

import trainerAaradhya from './images/trainer-aaradhya.jpg';
import trainerRohan from './images/trainer-rohan.jpg';
import trainerMeera from './images/trainer-meera.jpg';
import simpleHeroImg from './images/about-simple-hero.jpg';
import balconyImg from './images/about-hero-balcony.jpg';

function getYouTubeId(url) {
  if (!url) return null;
  try {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return match && match[2].length === 11 ? match[2] : null;
  } catch {
    return null;
  }
}

function getYouTubeEmbedUrl(url) {
  const id = getYouTubeId(url);
  if (id) {
    return `https://www.youtube.com/embed/${id}?autoplay=1&rel=0`;
  }
  if (url && url.includes('youtube.com/embed/')) {
    return url;
  }
  return null;
}

function isDirectVideoUrl(url) {
  return Boolean(url && /\.(mp4|webm|mov|m4v|ogg)(?:[?#].*)?$/i.test(url));
}

function formatSessionDate(dateStr) {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    return d.toLocaleString('en-US', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true,
    });
  } catch {
    return dateStr;
  }
}

export default function LiveSessionsPage() {
  // Authentication & Membership State from real account data
  const [currentUser, setCurrentUser] = useState(() => {
    try {
      const stored = localStorage.getItem('foursouls_user');
      return stored ? JSON.parse(stored) : null;
    } catch {
      return null;
    }
  });

  const [sessions, setSessions] = useState([]);
  const [loadingSessions, setLoadingSessions] = useState(true);
  const [selectedSessionId, setSelectedSessionId] = useState(null);
  const [activeReplayVideo, setActiveReplayVideo] = useState(null);
  const [filterType, setFilterType] = useState('all'); // 'all' | 'live' | 'video' | 'replay'
  const [copiedPasscodeId, setCopiedPasscodeId] = useState(null);

  // 1. Fetch current user from /api/auth/me on load
  useEffect(() => {
    fetch('/api/auth/me', {
      headers: { Accept: 'application/json' },
      credentials: 'include',
    })
      .then((res) => {
        if (res.ok) return res.json();
        if (res.status === 401) {
          localStorage.removeItem('foursouls_user');
          setCurrentUser(null);
        }
        return null;
      })
      .then((data) => {
        if (data?.user) {
          setCurrentUser(data.user);
          try {
            localStorage.setItem('foursouls_user', JSON.stringify(data.user));
          } catch {}
        }
      })
      .catch(() => {});
  }, []);

  // 2. Fetch admin-added live sessions & videos from backend
  useEffect(() => {
    setLoadingSessions(true);
    fetch('/api/live-sessions', {
      headers: { Accept: 'application/json' },
      credentials: 'include',
    })
      .then((res) => (res.ok ? res.json() : []))
      .then((data) => {
        const list = Array.isArray(data) ? data : (data?.data || []);
        setSessions(list);
        if (list.length > 0) {
          // Select first session with meeting link or video
          const firstWithContent = list.find((s) => s.meeting_url || s.video_url || s.video_file_url);
          setSelectedSessionId(firstWithContent ? firstWithContent.id : list[0].id);
        }
      })
      .catch((err) => {
        console.error('Failed to fetch live sessions:', err);
      })
      .finally(() => {
        setLoadingSessions(false);
      });
  }, [currentUser?.id, currentUser?.has_active_membership]);

  // Determine effective state strictly from account data
  let effectiveState = 'unauthenticated';
  if (!currentUser) {
    effectiveState = 'unauthenticated';
  } else if (currentUser.has_active_membership && !currentUser.membership?.is_expired) {
    effectiveState = 'active_member';
  } else if (currentUser.membership?.is_expired && currentUser.membership?.plan_slug) {
    effectiveState = 'expired_membership';
  } else {
    effectiveState = 'authenticated_no_plan';
  }

  // Selected featured session
  const featuredSession = sessions.find((s) => s.id === selectedSessionId) || sessions[0];

  // Counts for filter pills
  const liveCount = sessions.filter((s) => s.video_type === 'live_meeting' || s.meeting_url).length;
  const videoCount = sessions.filter(
    (s) => s.video_type === 'youtube' || s.video_type === 'uploaded_video' || (s.video_url && !s.meeting_url)
  ).length;
  const replayCount = sessions.filter((s) => s.video_type === 'replay' || s.replay_url).length;

  const filteredSessions = sessions.filter((s) => {
    if (filterType === 'live') return s.video_type === 'live_meeting' || s.meeting_url;
    if (filterType === 'video')
      return s.video_type === 'youtube' || s.video_type === 'uploaded_video' || (s.video_url && !s.meeting_url);
    if (filterType === 'replay') return s.video_type === 'replay' || s.replay_url;
    return true;
  });

  return (
    <main className="mockup-live-sessions-page">
      {/* Hero Header */}
      <section className="live-header-section">
        <div className="section-shell text-center">
          <div className="mockup-pill-badge badge-mint-soft">
            <span>DAILY LIVE PRACTICE & VIDEOS</span>
          </div>
          <h1 className="live-main-title">
            Real-Time Guidance. Sacred Community.
          </h1>
          <p className="live-subtitle">
            Practice live every day with FourSouls yoga masters. Experience personalized alignment corrections, breath synchronization, and exclusive masterclass video lessons.
          </p>
        </div>
      </section>

      {/* MAIN CONTENT AREA GATED STRICTLY BY MEMBERSHIP */}
      <section className="live-content-section">
        <div className="section-shell">
          {/* =========================================================================
              STATE 1: NOT SIGNED IN -> LOCK GATE
              ========================================================================= */}
          {effectiveState === 'unauthenticated' && (
            <div className="live-gate-card gate-signin">
              <div className="gate-lock-icon">🔒</div>
              <span className="mockup-pill-badge badge-mint-soft">MEMBER SIGN IN REQUIRED</span>
              <h2>Sign In to Join Today's Live Sessions & Videos</h2>
              <p>
                Our interactive daily live sessions, Zoom studio rooms, and video masterclasses are reserved exclusively for FourSouls members. Please sign in to your account to continue.
              </p>

              <div className="gate-actions-row">
                <AppLink href="/login" className="mockup-btn-navy">
                  <span>Sign In to Continue</span>
                  <span>→</span>
                </AppLink>
                <AppLink href="/register" className="btn-white-pill">
                  <span>Create Account</span>
                  <span>↗</span>
                </AppLink>
              </div>

              <div className="gate-preview-strip">
                <div className="preview-stat">
                  <strong>Live Zoom Studio</strong>
                  <small>Real-Time Teacher Feedback</small>
                </div>
                <div className="preview-stat-divider" />
                <div className="preview-stat">
                  <strong>HD Video Classes</strong>
                  <small>Curated YouTube & Masterclasses</small>
                </div>
                <div className="preview-stat-divider" />
                <div className="preview-stat">
                  <strong>24/7 Replays</strong>
                  <small>Never Miss a Sacred Practice</small>
                </div>
              </div>

              {/* Locked Sessions Preview */}
              <div className="locked-schedule-teaser">
                <h3>Admin Scheduled Sessions (Locked for Non-Members)</h3>
                <div className="locked-classes-grid">
                  {sessions.slice(0, 4).map((s) => (
                    <div key={s.id} className="locked-class-card">
                      <span className="locked-badge">🔒 Locked</span>
                      <h4>{s.title}</h4>
                      <p>{formatSessionDate(s.scheduled_start_at) || 'Scheduled Daily Class'}</p>
                    </div>
                  ))}
                  {false && sessions.length === 0 && (
                    <div className="locked-class-card">
                      <span className="locked-badge">🔒 Locked</span>
                      <h4>Sunrise Surya & Daily Pranayama Live Class</h4>
                      <p>Morning 7:00 AM – 7:45 AM · Zoom Studio</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}

          {/* =========================================================================
              STATE 2: SIGNED IN, BUT NO ACTIVE PLAN -> UPGRADE GATE
              ========================================================================= */}
          {effectiveState === 'authenticated_no_plan' && (
            <div className="live-gate-card gate-upgrade">
              <div className="gate-lock-icon">✨</div>
              <span className="mockup-pill-badge badge-mint-soft">MEMBERSHIP PLAN REQUIRED</span>
              <h2>Upgrade to FourSouls Membership</h2>
              <p>
                Hello {currentUser?.full_name || 'Practitioner'}! Your account is active, but you do not currently have an active membership plan. Live interactive Zoom classes, teacher meeting links, and recorded masterclass videos require an active plan.
              </p>

              <div className="gate-actions-row">
                <AppLink href="/plans" className="mockup-btn-navy">
                  <span>Explore Membership Plans</span>
                  <span>→</span>
                </AppLink>
              </div>

              <div className="upgrade-perks-grid">
                <div className="perk-box">
                  <span className="perk-check">✓</span>
                  <div>
                    <strong>Admin Zoom & Meeting Links</strong>
                    <p>Direct 1-click access to live classes with certified yoga teachers.</p>
                  </div>
                </div>
                <div className="perk-box">
                  <span className="perk-check">✓</span>
                  <div>
                    <strong>Exclusive Video Lessons</strong>
                    <p>Unlock HD video masterclasses, YouTube guided sessions, and replays.</p>
                  </div>
                </div>
                <div className="perk-box">
                  <span className="perk-check">✓</span>
                  <div>
                    <strong>Real-Time Posture Alignment</strong>
                    <p>Turn your camera on during live classes for personalized posture feedback.</p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* =========================================================================
              STATE 2B: MEMBERSHIP EXPIRED -> EXPIRED LOCK GATE
              ========================================================================= */}
          {effectiveState === 'expired_membership' && (
            <div className="live-gate-card gate-upgrade" style={{ borderTop: '4px solid #E11D48' }}>
              <div className="gate-lock-icon" style={{ background: '#FFE4E6', color: '#BE123C' }}>🔒</div>
              <span className="mockup-pill-badge" style={{ background: '#FFE4E6', color: '#BE123C' }}>MEMBERSHIP EXPIRED</span>
              <h2>Your Membership Has Expired</h2>
              <p>
                Hello {currentUser?.full_name || 'Practitioner'}! Your {currentUser?.membership?.plan_name ? `${currentUser.membership.plan_name} plan` : 'subscription'} ended on {currentUser?.membership?.expires_at || 'recently'}.
                Daily live interactive classes, Zoom studio links, and masterclass videos are locked once membership expires.
              </p>

              <div className="gate-actions-row">
                <AppLink href="/plans" className="mockup-btn-navy" style={{ background: '#E11D48' }}>
                  <span>Renew Membership Now</span>
                  <span>→</span>
                </AppLink>
                <AppLink href="/plans" className="btn-white-pill">
                  <span>View All Plans</span>
                  <span>↗</span>
                </AppLink>
              </div>

              <div className="upgrade-perks-grid">
                <div className="perk-box">
                  <span className="perk-check" style={{ color: '#E11D48' }}>✓</span>
                  <div>
                    <strong>Instant Access Restoration</strong>
                    <p>Renew today to immediately resume Zoom live links and video masterclasses.</p>
                  </div>
                </div>
                <div className="perk-box">
                  <span className="perk-check" style={{ color: '#E11D48' }}>✓</span>
                  <div>
                    <strong>Continuous Teacher Guidance</strong>
                    <p>Keep your daily momentum with certified master teachers.</p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* =========================================================================
              STATE 3: ACTIVE MEMBER -> UNLOCKED ADMIN ZOOM LINKS & VIDEOS
              ========================================================================= */}
          {effectiveState === 'active_member' && (
            <div className="live-member-unlocked-area">
              {/* Member Welcome Pill */}
              <div className="member-status-banner">
                <div className="status-indicator-live">
                  <span className="pulsing-dot" />
                  <strong>STUDIO ACCESS UNLOCKED</strong>
                </div>
                <span className="member-greeting">
                  Welcome back, <strong>{currentUser?.full_name || 'Mindful Member'}</strong> ✦ Active <strong>{currentUser?.membership?.plan_name || 'Yoga'}</strong> Plan ({currentUser?.membership?.days_remaining ?? 30} Days Left · Valid until {currentUser?.membership?.expires_at})
                </span>
              </div>

              {/* Main Featured Player / Studio Stage */}
              {featuredSession && (
                <div className="live-broadcast-card">
                  <div className="broadcast-video-frame">
                    {/* CASE A: YouTube Video */}
                    {featuredSession.video_url && getYouTubeEmbedUrl(featuredSession.video_url) ? (
                      <iframe
                        title={featuredSession.title}
                        src={getYouTubeEmbedUrl(featuredSession.video_url)}
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowFullScreen
                        className="broadcast-iframe"
                      />
                    ) : featuredSession.video_file_url || (featuredSession.video_type === 'external_video' && isDirectVideoUrl(featuredSession.video_url)) ? (
                      /* CASE B: Uploaded MP4 / WebM Video */
                      <video
                        controls
                        src={featuredSession.video_file_url || featuredSession.video_url}
                        className="broadcast-iframe"
                        poster={simpleHeroImg}
                      />
                    ) : (
                      /* CASE C: Live Zoom / Meeting Stage */
                      <div className="studio-meeting-hero">
                        <img src={balconyImg} alt="Studio Backdrop" className="studio-hero-bg" />
                        <div className="studio-hero-overlay">
                          <span className="studio-live-tag">
                            <span className="pulse-red" />
                            {featuredSession.provider === 'google_meet' ? 'GOOGLE MEET CLASS' : 'ZOOM LIVE CLASSROOM'}
                          </span>
                          <h3 className="studio-hero-title">{featuredSession.title}</h3>
                          <p className="studio-hero-subtitle">
                            {featuredSession.scheduled_start_at && `Starts: ${formatSessionDate(featuredSession.scheduled_start_at)}`}
                            {featuredSession.scheduled_end_at && (
                              <><br />Ends: {formatSessionDate(featuredSession.scheduled_end_at)}</>
                            )}
                          </p>

                          {featuredSession.meeting_url && (
                            <a
                              href={featuredSession.meeting_url}
                              target="_blank"
                              rel="noreferrer"
                              className="btn-join-zoom-hero"
                            >
                              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="23 7 16 12 23 17 23 7" />
                                <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                              </svg>
                              <span>Click to Enter {featuredSession.provider === 'google_meet' ? 'Google Meet' : 'Zoom'} Class</span>
                              <span>↗</span>
                            </a>
                          )}
                        </div>
                      </div>
                    )}

                    <div className="live-overlay-tag">
                      <span className="pulse-red" />
                      {featuredSession.video_url || featuredSession.video_file_url ? 'ON-DEMAND VIDEO' : 'LIVE STUDIO'}
                    </div>
                  </div>

                  {/* Broadcast Details Bar */}
                  <div className="broadcast-details-bar">
                    <div className="broadcast-details-left">
                      <span className="broadcast-category">
                        {featuredSession.video_type === 'youtube'
                          ? 'YOUTUBE MASTERCLASS'
                          : featuredSession.video_type === 'uploaded_video'
                          ? 'RECORDED LESSON'
                          : 'ADMIN SCHEDULED LIVE CLASS'}
                      </span>
                      <h2>{featuredSession.title}</h2>
                      {featuredSession.description && (
                        <p className="broadcast-instructor">{featuredSession.description}</p>
                      )}
                      <div className="broadcast-tags-row">
                        {featuredSession.scheduled_start_at && (
                          <span>🕒 Start: {formatSessionDate(featuredSession.scheduled_start_at)}</span>
                        )}
                        {featuredSession.scheduled_end_at && (
                          <span>⏱ End: {formatSessionDate(featuredSession.scheduled_end_at)}</span>
                        )}
                        <span>
                          {featuredSession.provider === 'google_meet'
                            ? 'Google Meet'
                            : featuredSession.provider === 'youtube'
                            ? 'YouTube HD'
                            : 'Zoom Studio'}
                        </span>
                        <span>Tier: {featuredSession.min_plan_tier?.toUpperCase() || 'ALL MEMBERS'}</span>
                      </div>
                    </div>

                    <div className="broadcast-actions-right">
                      {featuredSession.meeting_url ? (
                        <>
                          <a
                            href={featuredSession.meeting_url}
                            target="_blank"
                            rel="noreferrer"
                            className="btn-join-zoom-live"
                          >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                              <polygon points="23 7 16 12 23 17 23 7" />
                              <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                            </svg>
                            <span>Join {featuredSession.provider === 'google_meet' ? 'Google Meet' : 'Zoom Room'}</span>
                            <span>↗</span>
                          </a>

                          {featuredSession.meeting_password && (
                            <div className="zoom-room-id-badge">
                              <span>Passcode: <strong>{featuredSession.meeting_password}</strong></span>
                              <button
                                type="button"
                                className="btn-copy-code"
                                onClick={() => {
                                  navigator.clipboard?.writeText(featuredSession.meeting_password);
                                  setCopiedPasscodeId(featuredSession.id);
                                  setTimeout(() => setCopiedPasscodeId(null), 2000);
                                }}
                              >
                                {copiedPasscodeId === featuredSession.id ? '✓ Copied' : 'Copy'}
                              </button>
                            </div>
                          )}
                        </>
                      ) : featuredSession.video_url ? (
                        <a
                          href={featuredSession.video_url}
                          target="_blank"
                          rel="noreferrer"
                          className="btn-join-zoom-live"
                          style={{ background: '#DC2626' }}
                        >
                          <span>Open video</span>
                          <span>↗</span>
                        </a>
                      ) : null}
                    </div>
                  </div>
                </div>
              )}

              {/* =========================================================================
                  ADMIN LIVE SESSIONS & VIDEO LIBRARY
                  ========================================================================= */}
              <div className="live-schedule-section">
                <div className="schedule-header-row">
                  <div>
                    <span className="mockup-pill-badge badge-mint-soft">MEMBERS ACCESS</span>
                    <h3>Admin Added Live Classes & Videos</h3>
                  </div>

                  {/* Filter Pills */}
                  <div className="session-filter-tabs">
                    <button
                      type="button"
                      className={`session-filter-tab ${filterType === 'all' ? 'active' : ''}`}
                      onClick={() => setFilterType('all')}
                    >
                      All ({sessions.length})
                    </button>
                    <button
                      type="button"
                      className={`session-filter-tab ${filterType === 'live' ? 'active' : ''}`}
                      onClick={() => setFilterType('live')}
                    >
                      📹 Zoom / Meet ({liveCount})
                    </button>
                    <button
                      type="button"
                      className={`session-filter-tab ${filterType === 'video' ? 'active' : ''}`}
                      onClick={() => setFilterType('video')}
                    >
                      ▶️ Videos ({videoCount})
                    </button>
                    {replayCount > 0 && (
                      <button
                        type="button"
                        className={`session-filter-tab ${filterType === 'replay' ? 'active' : ''}`}
                        onClick={() => setFilterType('replay')}
                      >
                        ⏪ Replays ({replayCount})
                      </button>
                    )}
                  </div>
                </div>

                {loadingSessions ? (
                  <div className="p-8 text-center text-slate-500">
                    <p>Loading scheduled classes and videos...</p>
                  </div>
                ) : filteredSessions.length === 0 ? (
                  <div className="live-empty-state">
                    <p>No sessions found in this category. The admin will schedule upcoming live classes and upload new video lessons soon!</p>
                  </div>
                ) : (
                  <div className="admin-sessions-grid">
                    {filteredSessions.map((s, idx) => {
                      const isSelected = s.id === selectedSessionId;
                      const hasZoom = Boolean(s.meeting_url);
                      const hasVideo = Boolean(s.video_url || s.video_file_url);
                      const playableVideo = Boolean(s.video_file_url || getYouTubeEmbedUrl(s.video_url) || isDirectVideoUrl(s.video_url));
                      const ytId = getYouTubeId(s.video_url);

                      return (
                        <div
                          key={s.id}
                          className={`admin-session-card ${isSelected ? 'selected' : ''}`}
                        >
                          <div className="admin-session-thumb-wrap">
                            <img
                              src={
                                ytId
                                  ? `https://img.youtube.com/vi/${ytId}/mqdefault.jpg`
                                  : idx % 3 === 0
                                  ? trainerAaradhya
                                  : idx % 3 === 1
                                  ? trainerRohan
                                  : trainerMeera
                              }
                              alt={s.title}
                              className="admin-session-thumb"
                            />
                            <div className="admin-session-badge-top">
                              {s.video_type === 'youtube' ? (
                                <span className="badge-type badge-youtube">▶ YouTube HD</span>
                              ) : s.video_type === 'uploaded_video' || s.video_type === 'external_video' ? (
                                <span className="badge-type badge-video">📁 Video Class</span>
                              ) : s.video_type === 'replay' ? (
                                <span className="badge-type badge-replay">⏪ Replay</span>
                              ) : (
                                <span className="badge-type badge-live">
                                  📹 {s.provider === 'google_meet' ? 'Google Meet' : 'Zoom Live'}
                                </span>
                              )}
                              <span className="badge-tier">
                                {s.plan_badge || (s.min_plan_tier === 'all' ? 'All Plans' : s.min_plan_tier?.toUpperCase())}
                              </span>
                            </div>

                            {playableVideo && !s.is_locked && (
                              <button
                                type="button"
                                className="card-play-overlay-btn"
                                onClick={() => {
                                  setSelectedSessionId(s.id);
                                  if (s.video_url) {
                                    const embed = getYouTubeEmbedUrl(s.video_url);
                                    if (embed || isDirectVideoUrl(s.video_url)) setActiveReplayVideo(embed || s.video_url);
                                    else window.open(s.video_url, '_blank', 'noopener,noreferrer');
                                  } else if (s.video_file_url) {
                                    setActiveReplayVideo(s.video_file_url);
                                  }
                                }}
                                title="Play Video"
                              >
                                ▶
                              </button>
                            )}
                          </div>

                          <div className="admin-session-body">
                            <h4 className="admin-session-title">{s.title}</h4>
                            {s.description && (
                              <p className="admin-session-desc">{s.description}</p>
                            )}

                            <div className="admin-session-meta">
                              {s.scheduled_start_at && (
                                <span className="admin-session-time">
                                  🕒 Start: {formatSessionDate(s.scheduled_start_at)}
                                </span>
                              )}
                              {s.scheduled_end_at && (
                                <span className="admin-session-time">
                                  ⏱ End: {formatSessionDate(s.scheduled_end_at)}
                                </span>
                              )}
                            </div>

                            {/* Actions / Links */}
                            <div className="admin-session-actions">
                              {s.is_locked ? (
                                <div className="locked-session-card-msg">
                                  <span className="locked-badge-red">🔒 Locked for your plan</span>
                                  <p className="locked-subtext">Requires {s.plan_badge || 'Higher Plan'}</p>
                                  <AppLink href="/plans" className="btn-upgrade-mini">
                                    Upgrade Plan →
                                  </AppLink>
                                </div>
                              ) : (
                                <>
                                  {hasZoom && (
                                    <div className="zoom-action-box">
                                      <a
                                        href={s.meeting_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="btn-join-meeting-card"
                                      >
                                        <span>Join {s.provider === 'google_meet' ? 'Meet' : 'Zoom'}</span>
                                        <span>↗</span>
                                      </a>
                                      {s.meeting_password && (
                                        <div className="card-passcode-row">
                                          <span>Passcode: <strong>{s.meeting_password}</strong></span>
                                          <button
                                            type="button"
                                            className="btn-mini-copy"
                                            onClick={() => {
                                              navigator.clipboard?.writeText(s.meeting_password);
                                              setCopiedPasscodeId(s.id);
                                              setTimeout(() => setCopiedPasscodeId(null), 2000);
                                            }}
                                          >
                                            {copiedPasscodeId === s.id ? '✓' : 'Copy'}
                                          </button>
                                        </div>
                                      )}
                                    </div>
                                  )}

                                  {hasVideo && (
                                    <button
                                      type="button"
                                      className="btn-watch-video-card"
                                      onClick={() => {
                                        setSelectedSessionId(s.id);
                                        if (s.video_url) {
                                          const embed = getYouTubeEmbedUrl(s.video_url);
                                          if (embed || isDirectVideoUrl(s.video_url)) setActiveReplayVideo(embed || s.video_url);
                                          else window.open(s.video_url, '_blank', 'noopener,noreferrer');
                                        } else if (s.video_file_url) {
                                          setActiveReplayVideo(s.video_file_url);
                                        }
                                      }}
                                    >
                                      <span>Watch Video</span>
                                      <span>▶</span>
                                    </button>
                                  )}

                                  {s.replay_url && !hasVideo && (
                                    <a
                                      href={s.replay_url}
                                      target="_blank"
                                      rel="noreferrer"
                                      className="btn-watch-video-card"
                                    >
                                      <span>Watch Recording</span>
                                      <span>↗</span>
                                    </a>
                                  )}
                                </>
                              )}
                            </div>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>

              {/* Video Modal Player */}
              {activeReplayVideo && (
                <div
                  className="membership-modal-backdrop"
                  onClick={() => setActiveReplayVideo(null)}
                >
                  <div
                    className="membership-video-modal"
                    onClick={(e) => e.stopPropagation()}
                  >
                    <button
                      type="button"
                      className="modal-close-btn"
                      onClick={() => setActiveReplayVideo(null)}
                    >
                      ✕
                    </button>
                    <div className="video-player-frame">
                      {activeReplayVideo.includes('youtube.com') ? (
                        <iframe
                          title="Video Player"
                          src={activeReplayVideo}
                          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                          allowFullScreen
                        />
                      ) : (
                        <video controls autoPlay src={activeReplayVideo} style={{ width: '100%', height: '100%' }} />
                      )}
                    </div>
                  </div>
                </div>
              )}
            </div>
          )}
        </div>
      </section>
    </main>
  );
}
