import React, { useState, useEffect, useRef } from 'react';
import { AppLink } from './HomeBanner';

import aboutSimpleHeroImg from './images/about-simple-hero.jpg';
import heroBalconyImg from './images/about-hero-balcony.jpg';
import missionCobraImg from './images/about-mission-cobra.jpg';
import storyArchImg from './images/about-story-arch.jpg';
import ctaNatureBg from './images/cta-nature-bg.jpg';
import trainerAaradhya from './images/trainer-aaradhya.jpg';
import trainerRohan from './images/trainer-rohan.jpg';
import trainerMeera from './images/trainer-meera.jpg';
import guidePhoto from './images/image.png';

// Smooth animated counter component
function AnimatedCounter({ end, duration = 2000, suffix = '', prefix = '', decimals = 0, isStarted }) {
  const [count, setCount] = useState(0);

  useEffect(() => {
    if (!isStarted) return;
    let startTime = null;
    let frameId;

    const animate = (now) => {
      if (!startTime) startTime = now;
      const elapsed = now - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out exponential curve
      const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
      setCount(ease * end);

      if (progress < 1) {
        frameId = requestAnimationFrame(animate);
      } else {
        setCount(end);
      }
    };

    frameId = requestAnimationFrame(animate);
    return () => cancelAnimationFrame(frameId);
  }, [isStarted, end, duration]);

  const formatted = decimals > 0
    ? count.toFixed(decimals)
    : Math.floor(count).toLocaleString();

  return (
    <span className="animated-counter-num">
      {prefix}{formatted}{suffix}
    </span>
  );
}

export default function AboutUsPage() {
  const [guideModalOpen, setGuideModalOpen] = useState(false);
  const [statsStarted, setStatsStarted] = useState(false);
  const milestonesRef = useRef(null);
  const pageRef = useRef(null);

  useEffect(() => {
    // 1. Intersection Observer for scroll reveal animations
    const revealElements = pageRef.current?.querySelectorAll('.about-scroll-reveal');
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-in-view');
          }
        });
      },
      { threshold: 0.15, rootMargin: '0px 0px -50px 0px' }
    );

    if (revealElements) {
      revealElements.forEach((el) => observer.observe(el));
    }

    // 2. Intersection Observer specifically for milestones stats count-up
    const milestoneEl = milestonesRef.current;
    const statsObserver = new IntersectionObserver(
      (entries) => {
        const [entry] = entries;
        if (entry.isIntersecting) {
          setStatsStarted(true);
        }
      },
      { threshold: 0.25 }
    );

    if (milestoneEl) {
      statsObserver.observe(milestoneEl);
    }

    return () => {
      if (revealElements) {
        revealElements.forEach((el) => observer.unobserve(el));
      }
      if (milestoneEl) {
        statsObserver.unobserve(milestoneEl);
      }
    };
  }, []);

  return (
    <main className="mockup-about-page" ref={pageRef}>
      {/* 1. HERO BANNER: A Calmer Mind, A Healthier You */}
      <section className="about-mockup-hero" id="hero">
        <div className="about-mockup-hero-card about-scroll-reveal reveal-fade-in">
          <img
            src={aboutSimpleHeroImg}
            alt="A Calmer Mind, A Healthier You"
            className="about-hero-balcony-bg"
          />

          {/* Left Text Box Overlay */}
          <div className="about-hero-left-content about-scroll-reveal reveal-slide-right">
            <div className="mockup-pill-badge badge-mint-soft badge-pulse">
              <span>ABOUT FOURSOULS</span>
            </div>

            <h1 className="about-hero-mockup-heading">
              A Calmer Mind, <span className="highlight-dark-teal">A</span><br />
              <span className="highlight-dark-teal">Healthier You</span>
            </h1>

            <p className="about-hero-mockup-lead">
              At FourSouls, we believe yoga is more than exercise — it's a journey towards a calmer mind, a healthier body, and a more balanced life.
            </p>

            <div className="about-hero-actions-row">
              <a href="#story" className="mockup-btn-navy pulse-subtle">
                <span>Our Story</span>
                <span className="btn-arrow-slide">→</span>
              </a>

              <div className="mockup-social-proof-stack hover-glow-chip">
                <div className="social-proof-avatars">
                  <img src={trainerAaradhya} alt="Practitioner" className="avatar-chip" />
                  <img src={trainerRohan} alt="Practitioner" className="avatar-chip" />
                  <img src={trainerMeera} alt="Practitioner" className="avatar-chip" />
                </div>
                <div className="social-proof-text">
                  <strong>10,000+</strong>
                  <small>Mindful Practitioners</small>
                </div>
              </div>
            </div>
          </div>

          {/* Top-Right Calligraphy: Find Your Balance */}
          <div className="about-hero-calligraphy floating-ambient-slow">
            <span>Find</span>
            <span>Your Balance</span>
            <span className="calligraphy-leaf rotate-leaf">🌿</span>
          </div>
        </div>
      </section>

      {/* 2. OUR STORY: A Journey That Started with a Simple Belief */}
      <section className="about-mockup-story-section" id="story">
        <div className="section-shell">
          <div className="about-story-grid">
            {/* Left Content */}
            <div className="about-story-copy-col about-scroll-reveal reveal-slide-right">
              <div className="mockup-pill-badge badge-mint-soft">
                <span>OUR STORY</span>
              </div>

              <h2 className="about-section-heading">
                A Journey That Started with a Simple Belief
              </h2>

              <p className="about-story-paragraph">
                FourSouls was born from a simple belief — that everyone deserves access to the transformative power of yoga. What started as a small community has now grown into a global platform, helping thousands of people build healthier habits and happier lives.
              </p>

              <button
                type="button"
                className="mockup-btn-navy story-explore-btn"
                onClick={() => setGuideModalOpen(true)}
              >
                <span>Learn More</span>
                <span className="btn-arrow-slide">→</span>
              </button>
            </div>

            {/* Right Artwork: Dual Framed Composition */}
            <div className="about-story-visual-col about-scroll-reveal reveal-slide-left">
              <div className="story-dual-visual-wrapper">
                {/* Left Card: Terrace meditation visual */}
                <div className="story-visual-card card-studio card-hover-zoom">
                  <img src={heroBalconyImg} alt="Mindful Mediterranean Terrace Practice" className="story-card-img" />
                </div>

                {/* Right Card: Arched pose image */}
                <div className="story-visual-card card-arch card-hover-zoom">
                  <img src={storyArchImg} alt="Mindful Movement" className="story-card-img" />
                </div>

                {/* Floating Script: Same Breath, Brighter Lives */}
                <div className="story-floating-script floating-ambient">
                  <span>Same</span>
                  <span>Breath,</span>
                  <span>Brighter</span>
                  <span>Lives</span>
                  <span className="cursive-leaf">🌿</span>
                </div>

                {/* Floating White Pill: Yoga for a Better Tomorrow */}
                <div className="story-floating-tag floating-ambient hover-scale-card">
                  <span className="tag-icon-circle pulse-ring">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <circle cx="12" cy="12" r="9" />
                      <path d="M12 7v5l3 3" />
                    </svg>
                  </span>
                  <div className="tag-text">
                    <strong>Yoga for a</strong>
                    <small>Better Tomorrow</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 3. FOUR FEATURES ROW */}
      <section className="about-four-features-section">
        <div className="section-shell">
          <div className="about-four-features-strip about-scroll-reveal reveal-up">
            <div className="feature-item-col interactive-feature-card stagger-1">
              <div className="feature-icon-disc disc-float-1">
                <span className="feature-emoji">🍃</span>
              </div>
              <h3>Accessible to Everyone</h3>
              <p>High-quality yoga, anytime, anywhere.</p>
              <div className="feature-bottom-glow" />
            </div>

            <div className="feature-divider-vert" />

            <div className="feature-item-col interactive-feature-card stagger-2">
              <div className="feature-icon-disc disc-float-2">
                <span className="feature-emoji">🤍</span>
              </div>
              <h3>Guided by Experts</h3>
              <p>Learn from certified and experienced instructors.</p>
              <div className="feature-bottom-glow" />
            </div>

            <div className="feature-divider-vert" />

            <div className="feature-item-col interactive-feature-card stagger-3">
              <div className="feature-icon-disc disc-float-3">
                <span className="feature-emoji">👥</span>
              </div>
              <h3>A Supportive Community</h3>
              <p>Grow together with like-minded people.</p>
              <div className="feature-bottom-glow" />
            </div>

            <div className="feature-divider-vert" />

            <div className="feature-item-col interactive-feature-card stagger-4">
              <div className="feature-icon-disc disc-float-4">
                <span className="feature-emoji">📊</span>
              </div>
              <h3>A Healthier Tomorrow</h3>
              <p>Small, consistent steps for a brighter, balanced life.</p>
              <div className="feature-bottom-glow" />
            </div>
          </div>
        </div>
      </section>

      {/* 4. OUR MISSION: Yoga Accessible to Everyone, Everywhere */}
      <section className="about-mockup-mission-section" id="mission">
        <div className="section-shell">
          <div className="about-mission-grid">
            {/* Left Photo: Cobra pose on sunny wooden floor */}
            <div className="about-mission-visual-col about-scroll-reveal reveal-slide-right">
              <div className="mission-cobra-frame-wrapper">
                <div className="mission-cobra-frame card-hover-zoom">
                  <img src={missionCobraImg} alt="Yoga Accessible to Everyone" className="mission-cobra-img" />
                </div>

                {/* Floating Mind Body Soul Badge */}
                <div className="mission-mind-body-disc floating-ambient hover-scale-card">
                  <span>Mind</span>
                  <span>Body</span>
                  <span>Soul</span>
                  <span className="disc-leaf-deco">🌿</span>
                </div>
              </div>
            </div>

            {/* Right Content */}
            <div className="about-mission-copy-col about-scroll-reveal reveal-slide-left">
              <div className="mockup-pill-badge badge-mint-soft">
                <span>OUR MISSION</span>
              </div>

              <h2 className="about-section-heading">
                Yoga Accessible to Everyone, Everywhere
              </h2>

              <p className="about-mission-lead">
                Our mission is to make yoga accessible to everyone, everywhere. We create programs, live sessions, and resources that fit into your daily routine — whether you're a beginner or an experienced practitioner.
              </p>

              {/* Checklist with unique hover effects & staggered entrance */}
              <ul className="about-mission-checklist">
                <li className="mission-check-item stagger-1">
                  <span className="check-circle-green">✓</span>
                  <span className="check-text">Practical and easy-to-follow sessions</span>
                  <span className="check-arrow-hint">→</span>
                </li>
                <li className="mission-check-item stagger-2">
                  <span className="check-circle-green">✓</span>
                  <span className="check-text">Focus on real-life wellness, not just theory</span>
                  <span className="check-arrow-hint">→</span>
                </li>
                <li className="mission-check-item stagger-3">
                  <span className="check-circle-green">✓</span>
                  <span className="check-text">A safe and inclusive community</span>
                  <span className="check-arrow-hint">→</span>
                </li>
                <li className="mission-check-item stagger-4">
                  <span className="check-circle-green">✓</span>
                  <span className="check-text">Tools to support your journey beyond the mat</span>
                  <span className="check-arrow-hint">→</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* 5. WHAT DRIVES US (OUR VALUES RIBBON) */}
      <section className="about-values-ribbon-section">
        <div className="section-shell">
          <div className="values-section-header about-scroll-reveal reveal-up">
            <div className="mockup-pill-badge badge-mint-soft">
              <span>OUR VALUES</span>
            </div>
            <h2 className="about-section-heading">What Drives Us</h2>
            <p className="values-sub-lead">
              These values guide everything we do at FourSouls.
            </p>
          </div>

          <div className="values-ribbon-track about-scroll-reveal reveal-up">
            {/* Animated SVG connecting energy wave */}
            <svg className="ribbon-wavy-svg" viewBox="0 0 1000 90" preserveAspectRatio="none">
              <defs>
                <linearGradient id="valueLineGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                  <stop offset="0%" stopColor="#10B981" stopOpacity="0.4" />
                  <stop offset="35%" stopColor="#0E7465" stopOpacity="0.8" />
                  <stop offset="68%" stopColor="#0284C7" stopOpacity="0.8" />
                  <stop offset="100%" stopColor="#F59E0B" stopOpacity="0.8" />
                </linearGradient>
              </defs>
              {/* Background ambient path */}
              <path
                d="M 20 45 Q 260 15, 500 45 T 980 45"
                fill="none"
                stroke="rgba(14, 116, 101, 0.12)"
                strokeWidth="4"
              />
              {/* Animated flowing dashed stroke */}
              <path
                d="M 20 45 Q 260 15, 500 45 T 980 45"
                fill="none"
                stroke="url(#valueLineGrad)"
                strokeWidth="3.5"
                strokeDasharray="10 12"
                className="animated-wave-path"
              />
            </svg>

            <div className="values-nodes-grid">
              {/* Node 1: Mindfulness */}
              <div className="value-node-item node-mindfulness stagger-1">
                <div className="node-card-inner">
                  <div className="node-aura aura-green" />
                  <div className="node-icon-circle circle-green">
                    <span className="node-emoji">🍃</span>
                    <span className="node-ping-ring" />
                  </div>
                  <span className="node-badge-tag">Pillar 01</span>
                  <h4>Mindfulness</h4>
                  <p>Be present in every moment</p>
                </div>
              </div>

              {/* Node 2: Balance */}
              <div className="value-node-item node-balance stagger-2">
                <div className="node-card-inner">
                  <div className="node-aura aura-mint" />
                  <div className="node-icon-circle circle-mint">
                    <span className="node-emoji">🌱</span>
                    <span className="node-ping-ring" />
                  </div>
                  <span className="node-badge-tag">Pillar 02</span>
                  <h4>Balance</h4>
                  <p>Harmonize body, mind and soul</p>
                </div>
              </div>

              {/* Node 3: Inclusivity */}
              <div className="value-node-item node-inclusivity stagger-3">
                <div className="node-card-inner">
                  <div className="node-aura aura-teal" />
                  <div className="node-icon-circle circle-teal">
                    <span className="node-emoji">👥</span>
                    <span className="node-ping-ring" />
                  </div>
                  <span className="node-badge-tag">Pillar 03</span>
                  <h4>Inclusivity</h4>
                  <p>Yoga for every body, everywhere</p>
                </div>
              </div>

              {/* Node 4: Growth */}
              <div className="value-node-item node-growth stagger-4">
                <div className="node-card-inner">
                  <div className="node-aura aura-gold" />
                  <div className="node-icon-circle circle-gold">
                    <span className="node-emoji">✨</span>
                    <span className="node-ping-ring" />
                  </div>
                  <span className="node-badge-tag">Pillar 04</span>
                  <h4>Growth</h4>
                  <p>A healthier, brighter tomorrow</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 6. MILESTONES STATS CARD WITH COUNT-UP & HOVER 3D */}
      <section className="about-milestones-section" ref={milestonesRef}>
        <div className="section-shell">
          <div className="about-milestones-card about-scroll-reveal reveal-up">
            <div className="milestone-box milestone-interactive-box stagger-1">
              <div className="milestone-icon-badge badge-mint-soft">
                <span>🌱 Experience</span>
              </div>
              <strong className="milestone-stat-number">
                <AnimatedCounter isStarted={statsStarted} end={15} suffix="+" />
              </strong>
              <h4>Years of Teaching</h4>
              <p>Guided by experience and passion</p>
              <div className="milestone-shimmer" />
            </div>

            <div className="milestone-divider-vert animated-divider" />

            <div className="milestone-box milestone-interactive-box stagger-2">
              <div className="milestone-icon-badge badge-mint-soft">
                <span>👥 Community</span>
              </div>
              <strong className="milestone-stat-number">
                <AnimatedCounter isStarted={statsStarted} end={10000} suffix="+" />
              </strong>
              <h4>Mindful Practitioners</h4>
              <p>Across the world</p>
              <div className="milestone-shimmer" />
            </div>

            <div className="milestone-divider-vert animated-divider" />

            <div className="milestone-box milestone-interactive-box stagger-3">
              <div className="milestone-icon-badge badge-mint-soft">
                <span>🎥 Masterclasses</span>
              </div>
              <strong className="milestone-stat-number">
                <AnimatedCounter isStarted={statsStarted} end={500} suffix="+" />
              </strong>
              <h4>Live Masterclasses</h4>
              <p>Real-time guidance every month</p>
              <div className="milestone-shimmer" />
            </div>

            <div className="milestone-divider-vert animated-divider" />

            <div className="milestone-box milestone-interactive-box stagger-4">
              <div className="milestone-icon-badge badge-gold-soft">
                <span>⭐ Loved</span>
              </div>
              <strong className="milestone-stat-number rating-gold-stat">
                <AnimatedCounter isStarted={statsStarted} end={4.9} decimals={1} suffix=" ★" />
              </strong>
              <h4>Average Rating</h4>
              <p>Loved by our community</p>
              <div className="milestone-shimmer" />
            </div>
          </div>
        </div>
      </section>

      {/* 7. PRE-FOOTER CTA: Join Our Growing Community */}
      <section className="about-growing-community-cta">
        <div className="section-shell">
          <div className="growing-community-card">
            <img src={ctaNatureBg} alt="Lush Nature Sanctuary" className="growing-cta-bg-img" />
            <div className="growing-cta-overlay" />

            <div className="growing-cta-content-split">
              <div className="growing-cta-copy">
                <h2>Join Our Growing Community</h2>
                <p>
                  Start your journey towards a calmer, healthier and happier you — one mindful step at a time.
                </p>
              </div>

              <div className="growing-cta-action">
                <AppLink href="/plans" className="btn-white-pill">
                  <span>Start Free Trial</span>
                  <span className="btn-arrow-slide">→</span>
                </AppLink>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 8. MODAL: Meet Your Yoga Guide (Founder's Full Narrative) */}
      {guideModalOpen && (
        <div className="membership-modal-backdrop" onClick={() => setGuideModalOpen(false)}>
          <div className="guide-modal-dialog" onClick={(e) => e.stopPropagation()}>
            <button
              type="button"
              className="modal-close-btn"
              onClick={() => setGuideModalOpen(false)}
              aria-label="Close modal"
            >
              ✕
            </button>

            <div className="guide-modal-content">
              <div className="guide-modal-header">
                <span className="mockup-pill-badge badge-mint-soft">LEAD INSTRUCTOR</span>
                <h2>Meet Your Yoga Guide</h2>
                <p className="guide-experience-highlight">
                  15 Years of Experience. One Purpose: Helping You Make Health a Priority.
                </p>
              </div>

              <div className="guide-modal-body-grid">
                <div className="guide-modal-photo-col">
                  <img src={guidePhoto} alt="Yoga Guide" className="guide-modal-photo" />
                  <div className="guide-photo-caption">
                    <strong>15+ Years Teaching</strong>
                    <small>Certified Sound Healer & Master Instructor</small>
                  </div>
                </div>

                <div className="guide-modal-text-col">
                  <p>
                    With 15 years of experience as a yoga instructor, my journey has been shaped by a deep belief in the importance of caring for our physical, mental, and emotional well-being.
                  </p>
                  <p>
                    Over the years, I have come to believe that yoga is not just about performing postures or achieving flexibility. It is about creating a healthier relationship with ourselves through movement, breath, awareness, and consistency.
                  </p>
                  <p>
                    This belief inspires me to bring yoga to more people through our online platform, making it easier for everyone to begin and maintain a regular practice from the comfort of their own homes.
                  </p>
                  <div className="sound-healing-callout-card">
                    <span className="sound-icon-disc">✨</span>
                    <div>
                      <strong>Sound Healer & Mindful Experiences</strong>
                      <p>As a yoga instructor and sound healer, I am passionate about creating experiences that encourage movement, relaxation, mindfulness, and inner balance.</p>
                    </div>
                  </div>
                  <p>
                    My goal is to help you understand that you don't need hours of free time or a perfect routine to take care of yourself. Even small, consistent steps can help you build a more mindful and healthier lifestyle.
                  </p>
                  <div className="guide-invitation-box">
                    <p>
                      "I invite you to be part of this journey — to move with intention, breathe with awareness, and grow alongside a community that believes in making health a priority."
                    </p>
                    <strong className="guide-signature-note">
                      You don't have to be perfect. You just have to begin, and keep showing up for yourself.
                    </strong>
                  </div>

                  <div style={{ marginTop: '20px' }}>
                    <AppLink href="/plans" className="mockup-btn-primary full-width">
                      <span>Begin Your Practice Today →</span>
                    </AppLink>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </main>
  );
}
