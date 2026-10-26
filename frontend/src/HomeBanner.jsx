import React, { useState, useEffect } from 'react';
import { MEMBERSHIP_PRICING } from './membershipData';
import { CURATED_ARTICLES, resolveBlogImageUrl } from './JournalPage';
import CountryPricingBar from './CountryPricingBar';
import { getInitialCurrency } from './countryPricingService';

import heroBannerBg from './images/image copy.png';
import whyFoursoulsBannerBg from './images/image copy 2.png';
import sunsetMountainImg from './images/sunset-mountain.jpg';
import faqDecorImg from './images/faq-decor.jpg';
import ctaNatureBg from './images/cta-nature-bg.jpg';
import trainerAaradhya from './images/trainer-aaradhya.jpg';
import trainerRohan from './images/trainer-rohan.jpg';
import trainerMeera from './images/trainer-meera.jpg';
import trainerKaran from './images/trainer-karan.jpg';
import guidePhoto from './images/image.png';

export function AppLink({ href, children, className = '', onClick, target, rel }) {
  return (
    <a
      className={className}
      href={href}
      target={target}
      rel={rel}
      onClick={(event) => {
        if (href.startsWith('/') && !target) {
          event.preventDefault();
          window.history.pushState({}, '', href);
          window.dispatchEvent(new PopStateEvent('popstate'));
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        onClick?.();
      }}
    >
      {children}
    </a>
  );
}

let cachedHomeBlogs = null;

export default function HomeBanner({ data }) {
  const [membershipCurrency, setMembershipCurrency] = useState(getInitialCurrency); // 'inr' | 'aed' | 'usd'
  const [openFaq, setOpenFaq] = useState(0);
  const [videoModalOpen, setVideoModalOpen] = useState(false);
  const [activeTestimonialIndex, setActiveTestimonialIndex] = useState(0);
  const [homeBlogs, setHomeBlogs] = useState(() => cachedHomeBlogs || CURATED_ARTICLES.slice(0, 5));

  // Fetch latest 5 blogs from backend
  useEffect(() => {
    let isMounted = true;
    fetch('/api/blog?limit=5')
      .then((res) => (res.ok ? res.json() : []))
      .then((data) => {
        if (!isMounted || !Array.isArray(data) || data.length === 0) return;

        const fallbackImages = [sunsetMountainImg, heroBannerBg, whyFoursoulsBannerBg, faqDecorImg, ctaNatureBg];
        const fallbackAvatars = [trainerAaradhya, trainerRohan, trainerMeera, trainerKaran];

        const mapped = data.map((post, idx) => ({
          id: String(post.id),
          slug: post.slug,
          title: post.title,
          excerpt: post.excerpt || '',
          category: post.category || 'Yoga & Living',
          readTime: post.readTime || `${post.reading_time_minutes || 5} min read`,
          date: post.date || 'Recent',
          videoUrl: post.video_url || null,
          author: {
            name: post.author?.name || 'FourSouls Guide',
            role: post.author?.role || 'Yoga & Wellness Instructor',
            avatar: post.author?.avatar || fallbackAvatars[idx % fallbackAvatars.length],
          },
          image: resolveBlogImageUrl(post.image || post.featured_image_path, fallbackImages[idx % fallbackImages.length]),
        }));

        cachedHomeBlogs = mapped;
        setHomeBlogs(mapped);
      })
      .catch(() => {});

    return () => {
      isMounted = false;
    };
  }, []);

  const currKey = String(membershipCurrency || 'inr').toLowerCase();
  const activeCurrencyData = MEMBERSHIP_PRICING[currKey] || MEMBERSHIP_PRICING.inr;

  // Scroll Reveal Animations
  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
    );

    const elements = document.querySelectorAll('.anim-reveal');
    elements.forEach((el) => observer.observe(el));

    return () => observer.disconnect();
  }, []);

  const trainersList = [
    {
      name: 'Aaradhya Mehta',
      discipline: 'Hatha & Vinyasa Yoga',
      image: trainerAaradhya,
      link: '/courses',
    },
    {
      name: 'Rohan Kapoor',
      discipline: 'Ashtanga & Power Yoga',
      image: trainerRohan,
      link: '/courses',
    },
    {
      name: 'Meera Shah',
      discipline: 'Prenatal & Wellness Yoga',
      image: trainerMeera,
      link: '/courses',
    },
    {
      name: 'Karan Desai',
      discipline: 'Meditation & Breathwork',
      image: trainerKaran,
      link: '/courses',
    },
  ];

  const testimonials = [
    {
      quote: 'FourSouls has completely changed my lifestyle. I feel calmer, stronger and more focused.',
      author: 'Priya Sharma',
      city: 'Mumbai',
      avatar: trainerAaradhya,
    },
    {
      quote: 'The live sessions are amazing and the trainers are very supportive. Highly recommended!',
      author: 'Rahul Mehta',
      city: 'Bengaluru',
      avatar: trainerRohan,
    },
    {
      quote: 'A beautiful community with great guidance. The best decision for my wellness journey.',
      author: 'Neha Patel',
      city: 'Ahmedabad',
      avatar: trainerMeera,
    },
  ];

  const faqs = [
    {
      q: 'Do I need prior yoga experience?',
      a: 'Not at all! Over 60% of our members start as complete beginners. Every class offers gentle modifications and step-by-step alignment guidance.',
    },
    {
      q: 'Can I join live sessions from anywhere?',
      a: 'Yes, absolutely. Our live interactive sessions stream directly in your browser or mobile phone, wherever you are in the world.',
    },
    {
      q: 'What if I miss a live session?',
      a: 'Every live session is recorded and added to your on-demand library within 2 hours, so you can practice anytime at your convenience.',
    },
    {
      q: 'Is there a refund policy?',
      a: 'Yes! We offer a full 7-day unconditional money-back guarantee. If FourSouls is not the right fit for you, we will promptly refund your payment.',
    },
  ];


  return (
    <div className="mockup-page-wrapper">
      {/* 1. HERO BANNER */}
      <section className="mockup-hero-banner" id="hero">
        <div className="mockup-hero-bg-container">
          <img
            src={heroBannerBg}
            alt="Mindful yoga practitioner in serene Mediterranean terrace at golden hour"
            className="mockup-hero-bg-img"
          />
          <div className="mockup-hero-vignette" />
        </div>

        <div className="section-shell mockup-hero-shell">
          <div className="mockup-hero-col anim-reveal">
            {/* Pill Badge */}
            <div className="mockup-pill-badge anim-badge-pulse">
              <span className="pill-leaf">🍃</span>
              <span>A Healthier, Happier You</span>
            </div>

            {/* Editorial Heading */}
            <h1 className="mockup-hero-title">
              The Thoughtful Way to<br />
              Practice Yoga in <span className="mockup-teal-text">Your</span><br />
              Own Orbit.
            </h1>

            {/* Subtitle */}
            <p className="mockup-hero-sub">
              Daily live sessions, expert guidance, and a curated library designed to help you return to your body, calm your mind and build a practice that lasts.
            </p>

            {/* CTAs Row */}
            <div className="mockup-hero-actions">
              <AppLink href="/plans" className="mockup-btn-primary pulse-glow">
                <span>Start Free Trial</span>
                <span className="btn-arrow-slide">→</span>
              </AppLink>

              <button
                type="button"
                className="mockup-btn-video"
                onClick={() => setVideoModalOpen(true)}
                aria-label="Watch how FourSouls works"
              >
                <span className="mockup-play-disc">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z" />
                  </svg>
                </span>
                <span className="mockup-video-labels">
                  <strong>Watch Video</strong>
                  <small>See how it works</small>
                </span>
              </button>
            </div>

            {/* 3 Proof Badges */}
            <div className="mockup-hero-proofs">
              <div className="mockup-proof-item anim-proof-hover">
                <div className="mockup-avatars-overlap">
                  <img src={trainerAaradhya} alt="Member" className="avatar-disc" />
                  <img src={trainerRohan} alt="Member" className="avatar-disc" />
                  <img src={trainerMeera} alt="Member" className="avatar-disc" />
                </div>
                <div className="proof-text-stack">
                  <strong>10,000+</strong>
                  <small>Mindful Practitioners</small>
                </div>
              </div>

              <div className="mockup-proof-item anim-proof-hover">
                <span className="proof-icon-circle gold">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                  </svg>
                </span>
                <div className="proof-text-stack">
                  <strong>4.9/5</strong>
                  <small>Average Rating</small>
                </div>
              </div>

              <div className="mockup-proof-item anim-proof-hover">
                <span className="proof-icon-circle rose">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 2a5 5 0 0 1 5 5c0 3-5 8-5 8s-5-5-5-8a5 5 0 0 1 5-5z"></path>
                    <path d="M7 13c-2 0-5 2-5 5 0 2 2 4 5 4s5-2 5-4"></path>
                    <path d="M17 13c2 0 5 2 5 5 0 2-2 4-5 4s-5-2-5-4"></path>
                  </svg>
                </span>
                <div className="proof-text-stack">
                  <strong>Certified</strong>
                  <small>Yoga Masters</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 2. VALUE PROPOSITIONS STRIP (4 COLUMNS - COMPACT & SLEEK) */}
      <section className="mockup-values-strip">
        <div className="section-shell mockup-values-grid">
          <div className="mockup-value-col anim-reveal delay-1">
            <div className="value-icon-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v8M8 12h8" />
              </svg>
            </div>
            <h3>Live Yoga Sessions</h3>
            <p>Join interactive classes with expert guidance.</p>
          </div>

          <div className="mockup-value-col anim-reveal delay-2">
            <div className="value-icon-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                <path d="M12 2a5 5 0 0 1 5 5c0 3-5 8-5 8s-5-5-5-8a5 5 0 0 1 5-5z" />
                <path d="M7 13c-2 0-5 2-5 5 0 2 2 4 5 4s5-2 5-4" />
                <path d="M17 13c2 0 5 2 5 5 0 2-2 4-5 4s-5-2-5-4" />
              </svg>
            </div>
            <h3>Personalized Guidance</h3>
            <p>Practices designed for your goals and lifestyle.</p>
          </div>

          <div className="mockup-value-col anim-reveal delay-3">
            <div className="value-icon-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                <line x1="18" y1="20" x2="18" y2="10" />
                <line x1="12" y1="20" x2="12" y2="4" />
                <line x1="6" y1="20" x2="6" y2="14" />
              </svg>
            </div>
            <h3>Track Your Progress</h3>
            <p>Stay consistent and see real improvements.</p>
          </div>

          <div className="mockup-value-col anim-reveal delay-4">
            <div className="value-icon-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
              </svg>
            </div>
            <h3>A Supportive Community</h3>
            <p>Grow with people on the same journey.</p>
          </div>
        </div>
      </section>

      {/* 3. WHY FOURSOULS ("More Than Just Yoga, A Way of Life.") - FULL WIDTH */}
      <section className="mockup-why-section-fullwidth" id="why-foursouls">
        <div className="mockup-why-panoramic-card-fullwidth">
          {/* Full Panoramic Background Artwork Edge-to-Edge */}
          <img
            src={whyFoursoulsBannerBg}
            alt="Why FourSouls - More Than Just Yoga, A Way of Life"
            className="mockup-why-panoramic-bg"
          />

          {/* Panoramic Left Content Shell */}
          <div className="mockup-why-panoramic-content-shell">
            <div className="mockup-why-copy-box anim-reveal">
              <div className="mockup-pill-badge badge-mint-soft">
                <span>WHY FOURSOULS</span>
              </div>
              <h2 className="mockup-section-heading">
                More Than Just Yoga,<br />
                A Way of Life.
              </h2>
              <p className="mockup-lead-copy">
                At FourSouls, we believe yoga is not just a workout — it's a journey towards a calmer mind, a healthier body, and a more balanced life.
              </p>
              <AppLink href="/about" className="mockup-btn-navy">
                <span>Our Story</span>
                <span className="btn-arrow-slide">→</span>
              </AppLink>
            </div>
          </div>

          {/* Floating Interactive Badges Over the Right Artwork */}
          <div className="mockup-why-artwork-overlays-fullwidth">
            {/* Floating Balance Card */}
            <div className="mockup-floating-balance-card floating-ambient">
              <span className="balance-icon-disc">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <path d="M12 2a10 10 0 0 0 0 20" fill="currentColor" opacity="0.25" />
                  <circle cx="12" cy="7" r="1.5" fill="currentColor" />
                  <circle cx="12" cy="17" r="1.5" fill="currentColor" />
                </svg>
              </span>
              <div className="balance-card-text">
                <strong>Balance</strong>
                <small>Mind • Body • Soul</small>
              </div>
            </div>

            {/* Calligraphic Script 'Find Your Balance' */}
            <div className="mockup-why-calligraphy">
              <span>Find</span>
              <span>Your Balance</span>
              <span className="cursive-swirl-leaf">🌿</span>
            </div>
          </div>
        </div>
      </section>

      {/* 4. PRACTICE AT YOUR PACE ("Yoga for Every Lifestyle") */}
      <section className="mockup-lifestyle-section" id="practice">
        <div className="section-shell mockup-lifestyle-grid">
          <div className="mockup-lifestyle-intro anim-reveal">
            <div className="mockup-pill-badge badge-mint-soft">
              <span>PRACTICE AT YOUR PACE</span>
            </div>
            <h2 className="mockup-section-heading">
              Yoga for Every<br />Lifestyle
            </h2>
            <p className="mockup-lead-copy">
              Choose from live sessions, guided programs, and on-demand classes — all designed to fit into your daily routine, whether you're a beginner or an experienced practitioner.
            </p>
            <AppLink href="/courses" className="mockup-btn-primary">
              <span>Explore Classes</span>
              <span className="btn-arrow-slide">→</span>
            </AppLink>
          </div>

          <div className="mockup-lifestyle-cards">
            {/* Card 1 */}
            <div className="mockup-lifestyle-card anim-reveal delay-1">
              <div className="lifestyle-card-arch">
                <img src={trainerAaradhya} alt="Beginner yoga practice" />
              </div>
              <h3>Beginner-Friendly</h3>
              <p>Start your journey with simple and effective practices.</p>
            </div>

            {/* Card 2 */}
            <div className="mockup-lifestyle-card anim-reveal delay-2">
              <div className="lifestyle-card-arch">
                <img src={guidePhoto} alt="Live yoga sessions" />
              </div>
              <h3>Live Sessions</h3>
              <p>Practice with expert trainers in real-time.</p>
            </div>

            {/* Card 3 */}
            <div className="mockup-lifestyle-card anim-reveal delay-3">
              <div className="lifestyle-card-arch">
                <img src={faqDecorImg} alt="On-demand yoga library" />
              </div>
              <h3>On-Demand Library</h3>
              <p>Access anytime, anywhere at your convenience.</p>
            </div>
          </div>
        </div>
      </section>

      {/* 5. PANORAMIC MOUNTAIN SUNSET BANNER ("Small Daily Steps. Big, Lasting Change.") */}
      <section className="mockup-mountain-banner">
        <img
          src={sunsetMountainImg}
          alt="Meditating in mountain sunset"
          className="mockup-mountain-bg"
        />
        <div className="mockup-mountain-overlay" />
        <div className="section-shell mockup-mountain-content anim-reveal">
          <h2 className="mockup-mountain-title">
            Small Daily Steps.<br />
            Big, Lasting Change.
          </h2>

          <div className="mockup-mountain-stats-grid">
            <div className="mockup-mountain-stat-item anim-stat-card">
              <span className="stat-sparkle">✦</span>
              <strong>10,000+</strong>
              <span>Active Members</span>
              <div className="stat-hover-shimmer" />
            </div>
            <div className="mockup-mountain-stat-item anim-stat-card">
              <span className="stat-sparkle">✦</span>
              <strong>500+</strong>
              <span>Live Sessions</span>
              <div className="stat-hover-shimmer" />
            </div>
            <div className="mockup-mountain-stat-item anim-stat-card">
              <span className="stat-sparkle">✦</span>
              <strong>50+</strong>
              <span>Expert Trainers</span>
              <div className="stat-hover-shimmer" />
            </div>
            <div className="mockup-mountain-stat-item anim-stat-card">
              <span className="stat-sparkle">✦</span>
              <strong>4.9/5</strong>
              <span>Average Rating</span>
              <div className="stat-hover-shimmer" />
            </div>
          </div>
        </div>
      </section>

      {/* 6. MEET OUR EXPERT TRAINERS */}
      <section className="mockup-trainers-section" id="trainers">
        <div className="section-shell">
          <div className="mockup-trainers-top-row anim-reveal">
            <div>
              <div className="mockup-pill-badge badge-mint-soft">
                <span>OUR TRAINERS</span>
              </div>
              <h2 className="mockup-section-heading">Meet Our Expert Trainers</h2>
              <p className="mockup-lead-copy max-w-500">
                Learn from certified yoga instructors who bring years of experience and a passion for helping you grow.
              </p>
            </div>

            <div className="mockup-trainers-top-action">
              <div className="mockup-avatars-overlap">
                <img src={trainerAaradhya} alt="Trainer" className="avatar-disc" />
                <img src={trainerRohan} alt="Trainer" className="avatar-disc" />
                <img src={trainerMeera} alt="Trainer" className="avatar-disc" />
              </div>
              <AppLink href="/courses" className="mockup-btn-primary btn-sm">
                <span>View All Trainers</span>
                <span className="btn-arrow-slide">→</span>
              </AppLink>
            </div>
          </div>

          <div className="mockup-trainers-grid">
            {trainersList.map((tr, index) => (
              <div className={`mockup-trainer-card anim-reveal delay-${index + 1}`} key={tr.name}>
                <div className="trainer-photo-frame">
                  <img src={tr.image} alt={tr.name} className="trainer-photo" />
                </div>
                <div className="trainer-card-details">
                  <div>
                    <h3>{tr.name}</h3>
                    <p>{tr.discipline}</p>
                  </div>
                  <AppLink href={tr.link} className="trainer-arrow-btn" aria-label={`View classes by ${tr.name}`}>
                    →
                  </AppLink>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* 7. FOUR SOUL ONLINE YOGA MEMBERSHIP PLANS */}
      <section className="mockup-pricing-section" id="membership">
        <div className="section-shell">
          <div className="mockup-pricing-header anim-reveal">
            <div className="mockup-pill-badge badge-mint-soft">
              <span>MEMBERSHIP</span>
            </div>
            <h2 className="mockup-section-heading">FOUR SOUL ONLINE YOGA</h2>
            <p className="mockup-lead-copy">
              Live classes, every day · India, UAE and worldwide · The longer you commit, the more you save
            </p>

            {/* Country-Wise Pricing Controls */}
            <CountryPricingBar
              currency={membershipCurrency}
              onCurrencyChange={setMembershipCurrency}
            />
          </div>


          {/* 4-Tier Membership Grid */}
          <div className="home-membership-grid">
            {(activeCurrencyData?.plans || []).map((p, index) => {
              const isSadhana = p.id === 'sadhana';
              return (
                <div
                  key={p.id}
                  className={`home-plan-column anim-reveal delay-${index + 1} ${isSadhana ? 'tier-sadhana-home' : ''}`}
                >
                  {/* Column Header */}
                  <div className={`home-plan-header ${isSadhana ? 'header-sadhana' : 'header-standard'}`}>
                    <h3 className="tier-name">{p.name}</h3>
                    <span className="tier-duration">{p.duration}</span>
                    {p.savings && (
                      <span className="tier-badge-save">{p.savings}</span>
                    )}
                  </div>

                  {/* Strikethrough Row */}
                  <div className="home-plan-strikethrough">
                    {p.strikethrough ? (
                      <span className="strikethrough-price">{p.strikethrough}</span>
                    ) : (
                      <span className="strikethrough-empty">&nbsp;</span>
                    )}
                  </div>

                  {/* Big Price Display */}
                  <div className="home-plan-price-box">
                    <div className="price-big-number">
                      <span className="curr-sym">{activeCurrencyData.symbol}</span>
                      <span className="num-val">{p.price}</span>
                    </div>
                    <div className="per-month-text">{p.perMonth}</div>
                    <div className={`note-text ${p.savings ? 'highlight-save' : ''}`}>
                      {p.note}
                    </div>
                  </div>

                  {/* Plan Features */}
                  <ul className="home-plan-features">
                    {p.features.map((feat) => (
                      <li key={feat}>
                        <span className="feat-check">✓</span>
                        <span>{feat}</span>
                      </li>
                    ))}
                  </ul>

                  {/* Action Button */}
                  <div className="home-plan-action">
                    <AppLink
                      href="/plans"
                      className={isSadhana ? 'mockup-btn-primary full-width pulse-glow' : 'mockup-btn-navy full-width'}
                    >
                      <span>Join {p.name}</span>
                      <span className="btn-arrow-slide">→</span>
                    </AppLink>
                  </div>
                </div>
              );
            })}
          </div>

          {/* Official Footnote from Image */}
          <div className="home-membership-footnote anim-reveal">
            <p>
              The struck-through figure is what the same period costs when billed month by month at the 1-month rate.
              Every plan includes unlimited live classes and full library access; personal sessions, therapeutic tracks and plan-pause benefits increase with tenure.
            </p>
            <p className="launch-offer-alert">
              ✦ Launch pricing. Offer valid for a limited period. Full 7-day satisfaction guarantee.
            </p>
          </div>
        </div>
      </section>

      {/* 8. WHAT OUR MEMBERS SAY (TESTIMONIALS) */}
      <section className="mockup-testimonials-section" id="testimonials">
        <div className="section-shell">
          <div className="mockup-testimonials-header anim-reveal">
            <div>
              <div className="mockup-pill-badge badge-mint-soft">
                <span>TESTIMONIALS</span>
              </div>
              <h2 className="mockup-section-heading">What Our Members Say</h2>
            </div>
            <div className="mockup-carousel-controls">
              <button
                type="button"
                className="carousel-btn"
                onClick={() => setActiveTestimonialIndex((prev) => (prev > 0 ? prev - 1 : testimonials.length - 1))}
                aria-label="Previous testimonial"
              >
                ‹
              </button>
              <button
                type="button"
                className="carousel-btn"
                onClick={() => setActiveTestimonialIndex((prev) => (prev < testimonials.length - 1 ? prev + 1 : 0))}
                aria-label="Next testimonial"
              >
                ›
              </button>
            </div>
          </div>

          <div className="mockup-testimonials-grid">
            {testimonials.map((t, idx) => (
              <div className={`mockup-testimonial-card anim-reveal delay-${idx + 1} ${activeTestimonialIndex === idx ? 'highlighted-t' : ''}`} key={t.author}>
                <div className="testimonial-top">
                  <span className="testimonial-quote-icon">“</span>
                  <div className="testimonial-stars">★★★★★</div>
                </div>
                <p className="testimonial-quote-text">{t.quote}</p>
                <div className="testimonial-member-row">
                  <img src={t.avatar} alt={t.author} className="member-avatar" />
                  <div>
                    <strong>{t.author}</strong>
                    <small>{t.city}</small>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* 9. FROM THE JOURNAL: Mindful Wisdom & Daily Practices */}
      <section className="mockup-home-journal-section" id="journal">
        <div className="section-shell">
          <div className="home-journal-header anim-reveal">
            <div>
              <div className="mockup-pill-badge badge-mint-soft">
                <span>FROM THE JOURNAL</span>
              </div>
              <h2 className="mockup-section-heading">
                Words on Movement, Stillness & Daily Living
              </h2>
            </div>
            <AppLink href="/blog" className="home-journal-view-all">
              <span>Explore All Articles</span>
              <span className="btn-arrow-slide">→</span>
            </AppLink>
          </div>

          <div className="home-journal-cards-grid">
            {homeBlogs.slice(0, 5).map((article, idx) => (
              <AppLink
                key={article.id}
                href={`/blog/${article.slug}`}
                className="home-journal-card is-visible"
              >
                <div className="home-journal-thumb-wrap">
                  <img
                    src={resolveBlogImageUrl(article.image, sunsetMountainImg)}
                    alt={article.title}
                    onError={(e) => {
                      e.currentTarget.onerror = null;
                      e.currentTarget.src = sunsetMountainImg;
                    }}
                  />
                  <div className="card-top-badges">
                    <span className="home-journal-badge">{article.category}</span>
                    {article.videoUrl && (
                      <span className="home-journal-video-badge">▶ Video Guide</span>
                    )}
                  </div>
                </div>
                <div className="home-journal-body">
                  <div className="home-journal-meta">
                    <span>{article.date}</span>
                    <span>·</span>
                    <span>{article.readTime}</span>
                  </div>
                  <h3>{article.title}</h3>
                  <p>{article.excerpt}</p>
                  <div className="home-journal-footer">
                    <div className="home-journal-author">
                      <img
                        src={article.author?.avatar || trainerAaradhya}
                        alt={article.author?.name || 'Author'}
                      />
                      <span>{article.author?.name || 'FourSouls Guide'}</span>
                    </div>
                    <span className="home-journal-read-link">Read Note →</span>
                  </div>
                </div>
              </AppLink>
            ))}
          </div>
        </div>
      </section>

      {/* 10. FREQUENTLY ASKED QUESTIONS */}
      <section className="mockup-faq-section" id="faq">
        <div className="section-shell mockup-faq-grid">
          <div className="mockup-faq-col anim-reveal">
            <div className="faq-top-bar">
              <div className="mockup-pill-badge badge-mint-soft">
                <span>FAQ</span>
              </div>
              <AppLink href="/about" className="view-faq-link">
                View All FAQ →
              </AppLink>
            </div>

            <h2 className="mockup-section-heading">Frequently Asked Questions</h2>

            <div className="mockup-accordion-list">
              {faqs.map((faq, index) => {
                const isOpen = openFaq === index;
                return (
                  <div className={`mockup-faq-row ${isOpen ? 'is-open' : ''}`} key={faq.q}>
                    <button
                      type="button"
                      className="mockup-faq-btn"
                      onClick={() => setOpenFaq(isOpen ? null : index)}
                      aria-expanded={isOpen}
                    >
                      <span>{faq.q}</span>
                      <span className={`faq-toggle-sign ${isOpen ? 'is-rotated' : ''}`}>+</span>
                    </button>
                    {isOpen && <div className="mockup-faq-body">{faq.a}</div>}
                  </div>
                );
              })}
            </div>
          </div>

          {/* Artistic Enhanced FAQ Showcase */}
          <div className="mockup-faq-showcase-col anim-reveal delay-2">
            <div className="faq-organic-contour-wrapper floating-ambient">
              {/* Organic shaped background aura glow */}
              <div className="faq-blob-glow-backdrop" />
              
              {/* Contoured showcase frame */}
              <div className="faq-sculpted-frame">
                <img
                  src={faqDecorImg}
                  alt="Zen rolled mat and ceramic pottery"
                  className="faq-showcase-img"
                />
                
                {/* Elegant Cursive Script Overlay */}
                <div className="faq-script-overlay">
                  <span>Better</span>
                  <span>Breath,</span>
                  <span>Better You</span>
                </div>

                {/* Floating Glassmorphic Badge */}
                <div className="faq-wellness-pill-floating">
                  <span className="faq-pill-icon">🪷</span>
                  <div>
                    <strong>Daily Grounding</strong>
                    <small>Breath & Sound Healing</small>
                  </div>
                </div>
              </div>

              {/* Decorative Corner Leaf Accent */}
              <div className="faq-leaf-accent left-bottom">🌿</div>
              <div className="faq-leaf-accent right-top">✨</div>
            </div>
          </div>
        </div>
      </section>

      {/* 10. PRE-FOOTER CTA BANNER WITH NATURAL FOREST SANCTUARY BACKGROUND */}
      <section className="mockup-cta-banner">
        <div className="section-shell">
          <div className="mockup-cta-card">
            {/* Real Nature Photographic Background */}
            <img
              src={ctaNatureBg}
              alt="Serene mossy stone sanctuary in sunlit misty forest"
              className="mockup-cta-nature-bg"
            />
            {/* Cinematic Warm Dark Overlay for crisp contrast */}
            <div className="mockup-cta-overlay" />

            <div className="mockup-cta-inner anim-reveal">
              <div className="mockup-pill-badge badge-mint-soft">
                <span>READY TO BEGIN?</span>
              </div>
              <h2 className="mockup-cta-heading">
                Start Your Mindful Movement<br />
                Today with FourSouls
              </h2>
              <p className="mockup-cta-sub">
                Join thousands of practitioners and take the first step towards a healthier, happier you.
              </p>
              <div className="mockup-cta-action-wrap">
                <AppLink href="/plans" className="mockup-btn-primary btn-lg pulse-glow">
                  <span>Start Free Trial</span>
                  <span className="btn-arrow-slide">→</span>
                </AppLink>
                <small className="cta-disclaimer">No credit card required</small>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* VIDEO PREVIEW MODAL */}
      {videoModalOpen && (
        <div className="video-modal-backdrop" onClick={() => setVideoModalOpen(false)}>
          <div className="video-modal-card" onClick={(e) => e.stopPropagation()}>
            <button
              className="video-modal-close"
              onClick={() => setVideoModalOpen(false)}
              aria-label="Close video preview"
            >
              ✕
            </button>
            <div className="video-modal-header">
              <span className="modal-eyebrow">FOURSOULS SANCTUARY</span>
              <h3>The Thoughtful Way to Practice Yoga</h3>
              <p>Experience how our morning and evening sessions help you return to your body and feel grounded.</p>
            </div>
            <div className="video-modal-preview">
              <img src={heroBannerBg} alt="Video preview background" className="modal-video-bg" />
              <div className="modal-video-inner">
                <div className="modal-play-pulsing-btn">
                  <span className="pulse-wave" />
                  <span className="play-triangle">▶</span>
                </div>
                <h4>Mindful Movement • Daily Alignment • Sound Healing</h4>
                <p>Live daily sessions guided by master instructors.</p>
              </div>
            </div>
            <div className="video-modal-footer">
              <AppLink href="/plans" className="btn-modal-start" onClick={() => setVideoModalOpen(false)}>
                Start Free Trial ➔
              </AppLink>
              <span>Instant access • No obligations</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
