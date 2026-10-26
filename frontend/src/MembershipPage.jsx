import React, { useState, useEffect } from 'react';
import { MEMBERSHIP_PRICING } from './membershipData';
import CountryPricingBar from './CountryPricingBar';
import { getInitialCurrency } from './countryPricingService';

// Schedule timetable per timezone
const liveSchedules = {
  IST: [
    { time: '06:00 AM - 07:00 AM', name: 'Sunrise Surya & Pranayama', level: 'All Levels', teacher: 'Aarav Sharma' },
    { time: '07:30 AM - 08:30 AM', name: 'Dynamic Vinyasa Flow & Core', level: 'Intermediate', teacher: 'Maya Kapoor' },
    { time: '11:00 AM - 12:00 PM', name: 'Therapeutic Spine & Joint Mobility', level: 'Gentle', teacher: 'Aarav Sharma' },
    { time: '06:30 PM - 07:30 PM', name: 'Evening Stress Decompression', level: 'All Levels', teacher: 'Rhea Patel' },
    { time: '08:30 PM - 09:15 PM', name: 'Yoga Nidra & Guided Sound Meditation', level: 'Restorative', teacher: 'Karan Mehra' },
  ],
  GST: [
    { time: '04:30 AM - 05:30 AM', name: 'Sunrise Surya & Pranayama', level: 'All Levels', teacher: 'Aarav Sharma' },
    { time: '06:00 AM - 07:00 AM', name: 'Dynamic Vinyasa Flow & Core', level: 'Intermediate', teacher: 'Maya Kapoor' },
    { time: '09:30 AM - 10:30 AM', name: 'Therapeutic Spine & Joint Mobility', level: 'Gentle', teacher: 'Aarav Sharma' },
    { time: '05:00 PM - 06:00 PM', name: 'Evening Stress Decompression', level: 'All Levels', teacher: 'Rhea Patel' },
    { time: '07:00 PM - 07:45 PM', name: 'Yoga Nidra & Guided Sound Meditation', level: 'Restorative', teacher: 'Karan Mehra' },
  ],
  EST: [
    { time: '07:30 PM (Prev)', name: 'Sunrise Surya & Pranayama', level: 'All Levels', teacher: 'Aarav Sharma' },
    { time: '09:00 PM (Prev)', name: 'Dynamic Vinyasa Flow & Core', level: 'Intermediate', teacher: 'Maya Kapoor' },
    { time: '07:00 AM - 08:00 AM', name: 'Morning Spine & Joint Mobility', level: 'Gentle', teacher: 'Maya Kapoor' },
    { time: '09:00 AM - 10:00 AM', name: 'Awakening Vinyasa Flow', level: 'All Levels', teacher: 'Rhea Patel' },
    { time: '11:00 AM - 11:45 AM', name: 'Midday Breathwork & Meditation', level: 'Restorative', teacher: 'Karan Mehra' },
  ],
  GMT: [
    { time: '12:30 AM (Night)', name: 'Sunrise Surya & Pranayama', level: 'All Levels', teacher: 'Aarav Sharma' },
    { time: '02:00 AM (Night)', name: 'Dynamic Vinyasa Flow & Core', level: 'Intermediate', teacher: 'Maya Kapoor' },
    { time: '06:30 AM - 07:30 AM', name: 'Morning Spine & Joint Mobility', level: 'Gentle', teacher: 'Aarav Sharma' },
    { time: '01:00 PM - 02:00 PM', name: 'Afternoon Re-energize Flow', level: 'All Levels', teacher: 'Rhea Patel' },
    { time: '03:00 PM - 03:45 PM', name: 'Yoga Nidra & Guided Sound Meditation', level: 'Restorative', teacher: 'Karan Mehra' },
  ],
};

// Membership Comparison Matrix Rows
const comparisonFeatures = [
  { name: 'Unlimited Daily Live Classes', begin: 'Yes', commit: 'Yes', transform: 'Yes', sadhana: 'Yes' },
  { name: 'On-Demand Yoga & Pranayama Vault', begin: 'Yes', commit: 'Yes', transform: 'Yes', sadhana: 'Yes' },
  { name: 'Live Camera Posture Corrections', begin: 'Yes', commit: 'Yes', transform: 'Yes', sadhana: 'Yes' },
  { name: 'Private Community Hub Access', begin: 'Yes', commit: 'Yes', transform: 'Yes', sadhana: 'Yes' },
  { name: '90-Day Habit & Consistency Tracker', begin: '—', commit: 'Yes', transform: 'Yes', sadhana: 'Yes' },
  { name: 'Therapeutic Spine & Joint Masterclass', begin: '—', commit: '—', transform: 'Yes', sadhana: 'Yes' },
  { name: 'Plan-Pause Flexibility (Freeze Days)', begin: '0 Days', commit: '7 Days', transform: '14 Days', sadhana: 'Up to 45 Days' },
  { name: 'Complimentary 1-on-1 Master Guidance', begin: '—', commit: '—', transform: '1 Session', sadhana: '2x Private 1-on-1s' },
  { name: 'Weekend Sound Baths & Masterclasses', begin: '—', commit: '—', transform: 'Standard', sadhana: 'VIP Pass Included' },
  { name: 'Direct WhatsApp Teacher Hotline', begin: 'Standard', commit: 'Priority', transform: 'Priority', sadhana: 'Dedicated Master Concierge' },
];

// Frequently Asked Questions
const membershipFaqs = [
  {
    q: 'How does the Live Class schedule work across different time zones?',
    a: 'We host interactive morning and evening live sessions conducted by master teachers every single day. Whether you are in India (IST), the UAE (GST), the UK (GMT), or North America (EST), multiple live batch slots are available every day so you never miss a class.',
  },
  {
    q: 'What is the Plan-Pause flexibility and how do I freeze my subscription?',
    a: 'If you travel, fall ill, or need a personal break, you can pause your membership directly inside your student dashboard. Your remaining days freeze and do not expire. Commit plans have 7 pause days, Transform plans have 14 pause days, and Sadhana yearly members receive up to 45 pause days.',
  },
  {
    q: 'Can I upgrade or switch my plan later?',
    a: 'Yes, absolutely. You can upgrade from a 1-month or 3-month plan to Transform or Sadhana at any time. Any remaining days on your current plan will be credited towards your upgrade.',
  },
  {
    q: 'What equipment or previous experience do I need to begin?',
    a: 'Zero previous experience is required! All you need is a yoga mat and a device (phone, laptop, tablet, or smart TV). Our teachers provide real-time modifications for beginners as well as advanced posture variations.',
  },
  {
    q: 'What is the 7-day satisfaction money-back guarantee?',
    a: 'We want you to practice with complete peace of mind. Experience our live classes, teacher corrections, and on-demand library for 7 full days. If FourSouls is not the right fit for your orbit, email us within 7 days for a 100% full refund with no questions asked.',
  },
];

export default function MembershipPage() {
  const [currency, setCurrency] = useState(getInitialCurrency); // 'inr' | 'aed' | 'usd'
  const [selectedPlan, setSelectedPlan] = useState(null);
  const [checkoutModalOpen, setCheckoutModalOpen] = useState(false);
  const [checkoutSuccess, setCheckoutSuccess] = useState(false);
  const [checkoutError, setCheckoutError] = useState('');
  const [checkoutSubmitting, setCheckoutSubmitting] = useState(false);
  const [selectedTimezone, setSelectedTimezone] = useState('IST');
  const [openFaqIndex, setOpenFaqIndex] = useState(0);
  const [faqs, setFaqs] = useState(membershipFaqs);
  const [pricingData, setPricingData] = useState(MEMBERSHIP_PRICING);
  const [currentUser, setCurrentUser] = useState(() => {
    try {
      const stored = localStorage.getItem('foursouls_user');
      return stored ? JSON.parse(stored) : null;
    } catch {
      return null;
    }
  });

  const navigateTo = (to) => {
    window.history.pushState({}, '', to);
    window.dispatchEvent(new PopStateEvent('popstate'));
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  useEffect(() => {
    const handleAuth = () => {
      try {
        const stored = localStorage.getItem('foursouls_user');
        setCurrentUser(stored ? JSON.parse(stored) : null);
      } catch {
        setCurrentUser(null);
      }
    };
    window.addEventListener('foursouls_auth_changed', handleAuth);
    window.addEventListener('storage', handleAuth);

    fetch('/api/auth/me', { headers: { Accept: 'application/json' }, credentials: 'include' })
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
          localStorage.setItem('foursouls_user', JSON.stringify(data.user));
        }
      })
      .catch(() => {});

    return () => {
      window.removeEventListener('foursouls_auth_changed', handleAuth);
      window.removeEventListener('storage', handleAuth);
    };
  }, []);

  useEffect(() => {
    // 1. Fetch dynamic FAQs from Laravel backend
    fetch('/api/faqs')
      .then((res) => {
        if (!res.ok) throw new Error('API offline');
        return res.json();
      })
      .then((data) => {
        if (Array.isArray(data) && data.length > 0) {
          setFaqs(data);
        }
      })
      .catch((err) => {
        console.warn('Using default membership FAQs:', err);
      });

    // 2. Fetch dynamic subscription plans from Laravel backend
    fetch('/api/plans')
      .then((res) => {
        if (!res.ok) throw new Error('API offline');
        return res.json();
      })
      .then((data) => {
        if (Array.isArray(data) && data.length > 0) {
          setPricingData((prev) => {
            const next = { ...prev };
            ['inr', 'aed', 'usd'].forEach((currKey) => {
              if (!next[currKey]) return;
              next[currKey] = {
                ...next[currKey],
                plans: data.map((p) => ({
                  id: p.id,
                  name: p.name,
                  duration: p.duration,
                  price: currKey === 'inr' ? p.price_inr : currKey === 'aed' ? p.price_aed : p.price_usd,
                  strikethrough: currKey === 'inr' ? p.strikethrough_inr : currKey === 'aed' ? p.strikethrough_aed : p.strikethrough_usd,
                  perMonth: currKey === 'inr' ? p.per_month_inr : currKey === 'aed' ? p.per_month_aed : p.per_month_usd,
                  note: p.note,
                  savings: p.savings,
                  highlight: Boolean(p.highlight),
                  pause_days: p.pause_days,
                  features: Array.isArray(p.features) && p.features.length > 0 ? p.features : [
                    'Unlimited Daily Live Morning & Evening Classes',
                    'Full On-Demand Yoga & Pranayama Library',
                    'Live Alignment & Posture Correction',
                    'Private Community Hub Access',
                  ],
                })),
              };
            });
            return next;
          });
        }
      })
      .catch((err) => {
        console.warn('Using default membership pricing:', err);
      });
  }, []);

  const currentCurrencyData = pricingData[currency] || pricingData.inr;
  const sadhanaPlan = currentCurrencyData?.plans?.find((p) => p.id === 'sadhana') || currentCurrencyData?.plans?.[3] || currentCurrencyData?.plans?.[0] || {};

  const handleOpenCheckout = (plan, curr = currentCurrencyData) => {
    setSelectedPlan({ ...plan, currencyMeta: curr });
    setCheckoutModalOpen(true);
    setCheckoutSuccess(false);
    setCheckoutError('');
    setCheckoutSubmitting(false);
  };

  const handleConfirmOrder = async (e) => {
    e.preventDefault();
    setCheckoutError('');
    setCheckoutSubmitting(true);

    try {
      const csrfResponse = await fetch('/api/auth/csrf', {
        credentials: 'include',
        headers: { Accept: 'application/json' },
      });
      const csrfData = csrfResponse.ok ? await csrfResponse.json() : {};
      const csrfToken = csrfData.token || '';
      const formData = new FormData(e.currentTarget);
      const res = await fetch('/api/subscriptions/purchase', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken, 'X-XSRF-TOKEN': csrfToken } : {}),
        },
        credentials: 'include',
        body: JSON.stringify({
          plan_slug: selectedPlan.id,
          currency: currency.toUpperCase(),
          email: formData.get('email') || currentUser?.email,
        }),
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data?.user?.has_active_membership) {
        throw new Error(data?.message || 'Membership activation failed. Please sign in and try again.');
      }
      localStorage.setItem('foursouls_user', JSON.stringify(data.user));
      setCurrentUser(data.user);
      setCheckoutSuccess(true);
      window.dispatchEvent(new Event('foursouls_auth_changed'));
    } catch (err) {
      setCheckoutError(err?.message || 'Membership activation failed. Please try again.');
      setCheckoutSuccess(false);
    } finally {
      setCheckoutSubmitting(false);
    }
  };

  return (
    <main className="membership-page-wrapper">
      {/* 1. HERO HEADER */}
      <section className="membership-hero-header">
        <div className="section-shell">
          <div className="membership-header-badge">
            <span className="pill-leaf">🪷</span>
            <span>FOURSOUL YOGA MEMBERSHIP</span>
          </div>

          <h1 className="membership-main-heading">
            FOUR SOUL ONLINE YOGA
          </h1>

          <p className="membership-sub-heading">
            Live classes, every day · India, UAE and worldwide · The longer you commit, the more you save
          </p>

          {/* Localized Country Pricing Indicator */}
          <CountryPricingBar
            currency={currency}
            onCurrencyChange={setCurrency}
          />
        </div>

      </section>

      {/* ACTIVE MEMBERSHIP STATUS BANNER */}
      {currentUser && (
        <div className="active-membership-banner-container">
          {currentUser.has_active_membership && !currentUser.membership?.is_expired ? (
            <div className="active-membership-card">
              <div className="active-membership-left">
                <div className="status-badge-pulse">
                  <span className="live-dot" /> YOUR ACTIVE SUBSCRIPTION
                </div>
                <h2>
                  You are currently enrolled in the <span>{currentUser.membership?.plan_name || 'FourSouls'}</span> Plan
                </h2>
                <div className="active-membership-meta">
                  <span>📅 Valid Until: <strong>{currentUser.membership?.expires_at}</strong></span>
                  <span className="meta-sep">•</span>
                  <span>⏳ <strong>{currentUser.membership?.days_remaining} Days Remaining</strong></span>
                  <span className="meta-sep">•</span>
                  <span className="status-badge-active">Active</span>
                </div>
                <p className="active-membership-perks">
                  ✓ Unlimited daily live morning & evening masterclasses &nbsp;|&nbsp; ✓ Zoom meeting access &nbsp;|&nbsp; ✓ Full on-demand video vault
                </p>
              </div>
              <div className="active-membership-right">
                <button
                  type="button"
                  onClick={() => navigateTo('/live-sessions')}
                  className="btn-join-live-now"
                >
                  <span>Join Live Classes & Videos</span>
                  <span>➔</span>
                </button>
              </div>
            </div>
          ) : currentUser.membership?.is_expired && currentUser.membership?.plan_slug ? (
            <div className="membership-expired-alert-card">
              <div className="expired-alert-left">
                <div className="expired-alert-icon">⚠️</div>
                <div className="expired-alert-content">
                  <h3>
                    Your {currentUser.membership?.plan_name || 'Membership'} Expired on {currentUser.membership?.expires_at || 'Recently'}
                  </h3>
                  <p>
                    Live Zoom studio links and video masterclasses are locked once your membership expires. Please choose or renew a plan below to restore access.
                  </p>
                </div>
              </div>
              <a href="#plans-grid" className="btn-renew-membership-jump">
                <span>Renew Plan Below</span>
                <span>↓</span>
              </a>
            </div>
          ) : (
            <div className="membership-expired-alert-card" style={{ background: '#F0FDF4', borderColor: '#BBF7D0', borderLeftColor: '#16A34A' }}>
              <div className="expired-alert-left">
                <div className="expired-alert-icon" style={{ color: '#16A34A' }}>✨</div>
                <div className="expired-alert-content">
                  <h3 style={{ color: '#166534' }}>
                    Welcome, {currentUser.full_name || 'Practitioner'}! Select a Membership Plan to Begin Practice
                  </h3>
                  <p style={{ color: '#15803D' }}>
                    Choose any plan below to unlock daily live morning & evening Zoom sessions, teacher alignment corrections, and the complete video vault.
                  </p>
                </div>
              </div>
              <a href="#plans-grid" className="btn-renew-membership-jump" style={{ background: '#16A34A' }}>
                <span>View Plans Below</span>
                <span>↓</span>
              </a>
            </div>
          )}
        </div>
      )}

      {/* 2. THE OFFICIAL PRICING MATRIX */}
      <section className="membership-matrix-section" id="plans-grid">
        <div className="section-shell">
          {/* Interactive 4-Column Cards View */}
          <div className="pricing-matrix-container">
              {currentCurrencyData.plans.map((plan) => {
                const isSadhana = plan.id === 'sadhana';
                const userPlanSlug = currentUser?.membership?.plan_slug?.toLowerCase();
                const isMatchingPlan = Boolean(
                  userPlanSlug &&
                  (userPlanSlug === plan.id.toLowerCase() ||
                   userPlanSlug === plan.name.toLowerCase())
                );
                const isPlanActive = isMatchingPlan && currentUser?.has_active_membership && !currentUser?.membership?.is_expired;
                const isPlanExpired = isMatchingPlan && (currentUser?.membership?.is_expired || !currentUser?.has_active_membership);

                return (
                  <div
                    key={plan.id}
                    className={`matrix-tier-column ${isSadhana ? 'tier-sadhana' : ''} ${isPlanActive ? 'tier-user-active-plan' : ''}`}
                  >
                    {isPlanActive && (
                      <div className="user-active-plan-badge">
                        <span>✓ YOUR CURRENT ACTIVE PLAN</span>
                      </div>
                    )}
                    {isPlanExpired && (
                      <div className="user-active-plan-badge" style={{ background: '#E11D48' }}>
                        <span>⚠️ PREVIOUS PLAN (EXPIRED)</span>
                      </div>
                    )}

                    {/* Header Bar */}
                    <div className={`matrix-col-header ${isSadhana ? 'header-sadhana' : 'header-standard'}`}>
                      <h2 className="tier-name">{plan.name}</h2>
                      <span className="tier-duration">{plan.duration}</span>
                      {plan.savings && (
                        <div className="tier-badge-save">{plan.savings}</div>
                      )}
                    </div>

                    {/* Strikethrough Row */}
                    <div className="matrix-strikethrough-slot">
                      {plan.strikethrough ? (
                        <span className="strikethrough-price">{plan.strikethrough}</span>
                      ) : (
                        <span className="strikethrough-placeholder">&nbsp;</span>
                      )}
                    </div>

                    {/* Big Price Display */}
                    <div className="matrix-price-display">
                      <div className="price-big-number">
                        <span className="curr-sym">{currentCurrencyData.symbol}</span>
                        <span className="num-val">{plan.price}</span>
                      </div>
                      <div className="per-month-text">{plan.perMonth}</div>
                      <div className={`note-text ${plan.savings ? 'highlight-save' : ''}`}>
                        {plan.note}
                      </div>

                      {isPlanActive && (
                        <div className="active-period-indicator">
                          <span>Runs till <strong>{currentUser.membership?.expires_at}</strong></span>
                          <small>{currentUser.membership?.days_remaining} Days Remaining</small>
                        </div>
                      )}

                      {isPlanExpired && (
                        <div className="active-period-indicator indicator-expired">
                          <span>Expired on <strong>{currentUser.membership?.expires_at}</strong></span>
                          <small>Renew to restore live classes</small>
                        </div>
                      )}
                    </div>

                    {/* Features List */}
                    <ul className="matrix-features-list">
                      {plan.features.map((feat) => (
                        <li key={feat}>
                          <span className="feat-check">✓</span>
                          <span>{feat}</span>
                        </li>
                      ))}
                    </ul>

                    {/* Action Button */}
                    <div className="matrix-action-slot">
                      {isPlanActive ? (
                        <button
                          type="button"
                          className="btn-tier-select btn-current-active-plan"
                          disabled
                        >
                          <span>✓ Active Plan ({currentUser.membership?.days_remaining} Days Left)</span>
                        </button>
                      ) : isPlanExpired ? (
                        <button
                          type="button"
                          className="btn-tier-select btn-renew-plan"
                          onClick={() => handleOpenCheckout(plan)}
                        >
                          <span>Renew {plan.name} Plan</span>
                          <span className="arrow-slide">→</span>
                        </button>
                      ) : (
                        <button
                          type="button"
                          className={`btn-tier-select ${isSadhana ? 'btn-sadhana' : ''}`}
                          onClick={() => handleOpenCheckout(plan)}
                        >
                          <span>{currentUser?.has_active_membership ? `Switch to ${plan.name}` : `Join ${plan.name}`}</span>
                          <span className="arrow-slide">→</span>
                        </button>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>


          {/* Official Footnote from Image */}
          <div className="membership-official-footnote">
            <p>
              The struck-through figure is what the same period costs when billed month by month at the 1-month rate.
              Every plan includes unlimited live classes and full library access; personal sessions, therapeutic tracks and plan-pause benefits increase with tenure.
              Show one currency per visitor by location.
            </p>
            <p className="launch-offer-alert">
              ✦ Launch pricing. Offer valid for a limited period. Give every discount a reason and an end date — full 7-day money-back guarantee.
            </p>
          </div>
        </div>
      </section>

      {/* 3. INTERESTING SECTION: THE SADHANA YEARLY SPOTLIGHT */}
      <section className="membership-spotlight-section">
        <div className="section-shell">
          <div className="sadhana-spotlight-card">
            <div className="spotlight-left-content">
              <div className="spotlight-badge">
                <span>✦ RECOMMENDED FOR COMMITTED PRACTITIONERS</span>
              </div>
              <h2>Why 72% of Yogis Choose Sadhana Yearly</h2>
              <p className="spotlight-lede">
                Real, lasting transformation does not happen in 2 weeks. The Sadhana annual membership gives you the space, support, and flexibility to make yoga a seamless part of everyday life.
              </p>

              <div className="spotlight-perks-grid">
                <div className="spotlight-perk-item">
                  <div className="perk-icon-disc">🌱</div>
                  <div>
                    <strong>Just {currency === 'inr' ? '₹17' : currency === 'aed' ? 'AED 2.7' : '$0.95'} Per Day</strong>
                    <p>Less than your morning cup of tea for master-guided classes every day.</p>
                  </div>
                </div>

                <div className="spotlight-perk-item">
                  <div className="perk-icon-disc">⏸️</div>
                  <div>
                    <strong>Up to 45 Days Plan-Pause</strong>
                    <p>Traveling, busy, or taking exams? Freeze your account anytime with zero lost days.</p>
                  </div>
                </div>

                <div className="spotlight-perk-item">
                  <div className="perk-icon-disc">🧘‍♂️</div>
                  <div>
                    <strong>2x Private Master Consultations</strong>
                    <p>Exclusive 1-on-1 personalized spine, mobility, and posture diagnostic audit.</p>
                  </div>
                </div>

                <div className="spotlight-perk-item">
                  <div className="perk-icon-disc">✨</div>
                  <div>
                    <strong>Weekend Masterclasses Included</strong>
                    <p>Free entry into all monthly sound baths, breathwork intensives, and guest workshops.</p>
                  </div>
                </div>
              </div>

              <div className="spotlight-action-row">
                <button
                  type="button"
                  className="btn-sadhana-large"
                  onClick={() => handleOpenCheckout(sadhanaPlan)}
                >
                  <span>Begin Sadhana Yearly ({currentCurrencyData.symbol}{sadhanaPlan.price || '6,499'})</span>
                  <span>→</span>
                </button>
                <span className="guarantee-micro">7-Day Risk Free Money-Back Guarantee</span>
              </div>
            </div>

            <div className="spotlight-right-card">
              <div className="sadhana-quote-card">
                <div className="quote-badge">PRACTITIONER STORY</div>
                <p className="quote-body">
                  "Committing to the Sadhana annual plan was the best decision of my year. Having teachers correct my camera posture in real-time cured 5 years of desk slouching."
                </p>
                <div className="quote-author-row">
                  <div className="author-avatar-chip">AS</div>
                  <div>
                    <strong>Ananya Singhania</strong>
                    <small>Sadhana Member · 14 Months</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 4. INTERESTING SECTION: COMPREHENSIVE FEATURE COMPARISON MATRIX */}
      <section className="membership-matrix-comparison-section">
        <div className="section-shell">
          <div className="comparison-header">
            <span className="section-eyebrow">DETAILED COMPARISON</span>
            <h2>Compare All Membership Plans</h2>
            <p>Hover over any feature row to compare inclusions across all 4 tiers.</p>
          </div>

          <div className="comparison-table-wrapper">
            <table className="comparison-table">
              <thead>
                <tr>
                  <th className="feature-col-th">Plan Features</th>
                  <th className="th-col-begin">
                    <span className="th-name">BEGIN</span>
                    <span className="th-dur">1 Month</span>
                  </th>
                  <th className="th-col-commit">
                    <span className="th-name">COMMIT</span>
                    <span className="th-dur">3 Months</span>
                  </th>
                  <th className="th-col-transform">
                    <span className="th-name">TRANSFORM</span>
                    <span className="th-dur">6 Months</span>
                  </th>
                  <th className="th-sadhana th-col-sadhana">
                    <span className="th-badge-popular">BEST VALUE</span>
                    <span className="th-name">SADHANA</span>
                    <span className="th-dur">Yearly</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {comparisonFeatures.map((row, idx) => (
                  <tr key={row.name} className={`comparison-interactive-row ${idx % 2 === 0 ? 'row-even' : 'row-odd'}`}>
                    <td className="feature-name-cell">
                      <span className="feature-indicator-bullet" />
                      <strong>{row.name}</strong>
                    </td>
                    <td className="cell-tier cell-begin">
                      {row.begin === 'Yes' ? <span className="tick-yes">✓</span> : <span className="dash-no">{row.begin}</span>}
                    </td>
                    <td className="cell-tier cell-commit">
                      {row.commit === 'Yes' ? <span className="tick-yes">✓</span> : <span className="dash-no">{row.commit}</span>}
                    </td>
                    <td className="cell-tier cell-transform">
                      {row.transform === 'Yes' ? <span className="tick-yes">✓</span> : <span className="feature-highlight-val">{row.transform}</span>}
                    </td>
                    <td className="td-sadhana cell-tier cell-sadhana">
                      {row.sadhana === 'Yes' ? (
                        <span className="tick-yes bold-green">✓</span>
                      ) : (
                        <strong className="sadhana-perk-text">{row.sadhana}</strong>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </section>

      {/* 5. INTERESTING SECTION: THE 4-STAGE MIND-BODY EVOLUTION JOURNEY */}
      <section className="membership-evolution-section">
        <div className="section-shell">
          <div className="evolution-header">
            <span className="section-eyebrow">YOUR PRACTICE TIMELINE</span>
            <h2>The FourSouls Evolution Journey</h2>
            <p>What happens to your body, breath, and mindset at each stage of your membership.</p>
          </div>

          <div className="evolution-steps-grid">
            {/* STAGE 1: SUNRISE CORAL */}
            <div className="evolution-card evo-stage-1">
              <div className="evolution-card-top">
                <div className="evolution-stage-num num-coral">01</div>
                <div className="stage-icon-disc disc-coral">🌅</div>
              </div>
              <span className="stage-pill pill-coral">MONTH 1 · BEGIN</span>
              <h3>Awakening & Foundations</h3>
              <p>
                Open tight hip flexors and decompress spinal compression. Learn yogic breath synchronization and establish your body awareness.
              </p>
              <ul className="stage-bullets bullets-coral">
                <li>Eliminate morning stiffness</li>
                <li>Learn correct joint alignment</li>
                <li>Deeper, calmer sleep cycles</li>
              </ul>
              <div className="evo-card-accent-line line-coral" />
            </div>

            {/* STAGE 2: OCEAN TEAL */}
            <div className="evolution-card evo-stage-2">
              <div className="evolution-card-top">
                <div className="evolution-stage-num num-teal">02</div>
                <div className="stage-icon-disc disc-teal">🌊</div>
              </div>
              <span className="stage-pill pill-teal">MONTH 2-3 · COMMIT</span>
              <h3>Habit & Core Stability</h3>
              <p>
                Practice becomes second nature. Your posture stands naturally tall and your body develops functional core stability without strain.
              </p>
              <ul className="stage-bullets bullets-teal">
                <li>Automatic daily yoga routine</li>
                <li>Noticeable reduction in lower back pain</li>
                <li>Increased mental focus during work</li>
              </ul>
              <div className="evo-card-accent-line line-teal" />
            </div>

            {/* STAGE 3: AMETHYST PURPLE */}
            <div className="evolution-card evo-stage-3">
              <div className="evolution-card-top">
                <div className="evolution-stage-num num-purple">03</div>
                <div className="stage-icon-disc disc-purple">🦋</div>
              </div>
              <span className="stage-pill pill-purple">MONTH 4-6 · TRANSFORM</span>
              <h3>Deep Mobility & Nervous Balance</h3>
              <p>
                Move with effortless fluidity. As cortisol levels lower, your nervous system responds with mental clarity and physical grace.
              </p>
              <ul className="stage-bullets bullets-purple">
                <li>Advance into dynamic flow sequences</li>
                <li>Regulated nervous system & lower stress</li>
                <li>Noticeable cardiovascular resilience</li>
              </ul>
              <div className="evo-card-accent-line line-purple" />
            </div>

            {/* STAGE 4: EMERALD SAGE GOLD */}
            <div className="evolution-card evo-stage-4 evolution-sadhana-card">
              <div className="evolution-card-top">
                <div className="evolution-stage-num num-sadhana">04</div>
                <div className="stage-icon-disc disc-sadhana">🪷</div>
              </div>
              <span className="stage-pill pill-sadhana">MONTH 7-12 · SADHANA</span>
              <h3>Lifelong Harmony & Mastery</h3>
              <p>
                Yoga is no longer an exercise routine — it is how you live, think, and breathe. Complete physical and mental equilibrium.
              </p>
              <ul className="stage-bullets bullets-sadhana">
                <li>Lifelong joint preservation</li>
                <li>Unshakeable calm under pressure</li>
                <li>Deepest holistic transformation</li>
              </ul>
              <div className="evo-card-accent-line line-sadhana" />
            </div>
          </div>
        </div>
      </section>

      {/* 6. INTERESTING SECTION: LIVE SCHEDULE PREVIEW ACROSS TIMEZONES */}
      <section className="membership-schedule-section">
        <div className="section-shell">
          <div className="schedule-box">
            <div className="schedule-header">
              <div>
                <span className="section-eyebrow">DAILY LIVE TIMETABLE</span>
                <h2>Never Miss a Live Masterclass</h2>
                <p>Interactive sessions held daily with real-time feedback.</p>
              </div>

              {/* Timezone Switcher */}
              <div className="timezone-toggle-pills">
                {['IST', 'GST', 'EST', 'GMT'].map((tz) => (
                  <button
                    key={tz}
                    type="button"
                    className={`tz-btn ${selectedTimezone === tz ? 'active' : ''}`}
                    onClick={() => setSelectedTimezone(tz)}
                  >
                    {tz} Time
                  </button>
                ))}
              </div>
            </div>

            <div className="schedule-list">
              {liveSchedules[selectedTimezone].map((slot) => (
                <div key={slot.name} className="schedule-item-row">
                  <div className="slot-time-badge">
                    <span>⏰</span>
                    <strong>{slot.time}</strong>
                  </div>
                  <div className="slot-details">
                    <h4>{slot.name}</h4>
                    <span className="slot-teacher">Guided by {slot.teacher}</span>
                  </div>
                  <div className="slot-tag-pill">
                    <span>{slot.level}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* 7. FOURSOULS MEMBERSHIP GUARANTEES */}
      <section className="membership-guarantee-strip-section">
        <div className="section-shell">
          <div className="membership-guarantee-strip">
            <div className="guarantee-item">
              <span className="guarantee-icon">🛡️</span>
              <div>
                <strong>7-Day Risk-Free Guarantee</strong>
                <p>Try our live classes for a full week. If it's not right for you, receive a 100% refund with zero questions.</p>
              </div>
            </div>

            <div className="guarantee-item">
              <span className="guarantee-icon">⏸️</span>
              <div>
                <strong>Flexi-Pause Subscription</strong>
                <p>Pause up to 45 days inside your student dashboard without penalty whenever life gets busy.</p>
              </div>
            </div>

            <div className="guarantee-item">
              <span className="guarantee-icon">🔒</span>
              <div>
                <strong>Instant & Secure Payment</strong>
                <p>Protected by 256-Bit SSL encryption with UPI, RuPay, Visa, MasterCard, Apple Pay & Google Pay.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 8. MEMBERSHIP FAQ */}
      <section className="membership-faq-section">
        <div className="section-shell">
          <div className="m-faq-header">
            <span className="section-eyebrow">COMMON QUESTIONS</span>
            <h2>Everything You Need to Know</h2>
            <p>Clear answers regarding billing, pausing, and practicing with FourSouls.</p>
          </div>

          <div className="m-faq-accordion">
            {faqs.map((faq, idx) => {
              const isOpen = openFaqIndex === idx;
              return (
                <div key={faq.q} className={`m-faq-card ${isOpen ? 'is-open' : ''}`}>
                  <button
                    type="button"
                    className="m-faq-question-btn"
                    onClick={() => setOpenFaqIndex(isOpen ? -1 : idx)}
                  >
                    <span>{faq.q}</span>
                    <span className="faq-toggle-cross">{isOpen ? '−' : '+'}</span>
                  </button>
                  {isOpen && (
                    <div className="m-faq-answer-body">
                      <p>{faq.a}</p>
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* INTERACTIVE CHECKOUT MODAL */}
      {checkoutModalOpen && selectedPlan && (
        <div className="membership-modal-backdrop" onClick={() => setCheckoutModalOpen(false)}>
          <div className="membership-modal-dialog" onClick={(e) => e.stopPropagation()}>
            <button
              type="button"
              className="modal-close-btn"
              onClick={() => setCheckoutModalOpen(false)}
              aria-label="Close modal"
            >
              ✕
            </button>

            {checkoutSuccess ? (
              <div className="checkout-success-view">
                <div className="success-check-disc">✓</div>
                <h3>Welcome to FourSouls!</h3>
                <p>
                  Your <strong>{selectedPlan.name} ({selectedPlan.duration})</strong> membership is now active.
                </p>
                <div className="order-receipt-chip">
                  <span>Amount Paid: </span>
                  <strong>{selectedPlan.currencyMeta.symbol}{selectedPlan.price}</strong>
                </div>
                <small style={{ color: '#00877A', display: 'block', marginTop: '16px' }}>
                  A confirmation email with your class Zoom links and library access has been dispatched.
                </small>
              </div>
            ) : (
              <div>
                <span className="eyebrow" style={{ color: '#00877A', marginBottom: '6px' }}>SECURE ENROLLMENT</span>
                <h3 style={{ margin: '0 0 10px', fontSize: '22px', color: '#172A3A' }}>
                  Enroll in {selectedPlan.name} Plan
                </h3>

                <div className="membership-order-card">
                  <div className="order-row">
                    <span>Plan Duration</span>
                    <strong>{selectedPlan.duration}</strong>
                  </div>
                  <div className="order-row">
                    <span>Plan Rate</span>
                    <strong className="order-price">{selectedPlan.currencyMeta.symbol}{selectedPlan.price}</strong>
                  </div>
                  <div className="order-row">
                    <span>Effective Monthly</span>
                    <small>{selectedPlan.perMonth}</small>
                  </div>
                  {selectedPlan.savings && (
                    <div className="order-row">
                      <span>Discount</span>
                      <strong style={{ color: '#C05621' }}>{selectedPlan.savings}</strong>
                    </div>
                  )}
                </div>

                <form onSubmit={handleConfirmOrder} className="membership-checkout-form">
                  <div className="checkout-input-group">
                    <label>Full Name</label>
                    <input name="full_name" type="text" placeholder="e.g. Dhruv Patel" required defaultValue={currentUser?.full_name || "Dhruv Patel"} />
                  </div>

                  <div className="checkout-input-group">
                    <label>Email Address (For Live Class Links)</label>
                    <input name="email" type="email" placeholder="dhruv@example.com" required defaultValue={currentUser?.email || "dhruv@example.com"} />
                  </div>

                  <div className="checkout-input-group">
                    <label>Phone / WhatsApp Number</label>
                    <input name="phone" type="tel" placeholder="+91 98765 43210" required defaultValue="+91 98765 43210" />
                  </div>

                  {checkoutError && <p className="login-error" role="alert">{checkoutError}</p>}

                  <button type="submit" className="btn-confirm-membership" disabled={checkoutSubmitting}>
                    Confirm & Start Practice ({selectedPlan.currencyMeta.symbol}{selectedPlan.price}) ➔
                  </button>

                  <small style={{ textAlign: 'center', color: '#64748B', display: 'block', marginTop: '8px' }}>
                    🔒 256-Bit SSL Encrypted · 7-Day Unconditional Guarantee
                  </small>
                </form>
              </div>
            )}
          </div>
        </div>
      )}
    </main>
  );
}
