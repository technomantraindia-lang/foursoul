import { StrictMode, useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import './styles.css';
import HomeBanner from './HomeBanner';
import MembershipPage from './MembershipPage';
import AboutUsPage from './AboutUsPage';
import JournalPage from './JournalPage';
import LiveSessionsPage from './LiveSessionsPage';

import heroBannerBg from './images/image copy.png';
import guidePhoto from './images/image.png';
import logoImage from './images/logo.png';
import whyFoursoulsImg from './images/why-foursouls.jpg';
import sunsetMountainImg from './images/sunset-mountain.jpg';
import faqDecorImg from './images/faq-decor.jpg';
import trainerAaradhya from './images/trainer-aaradhya.jpg';
import trainerRohan from './images/trainer-rohan.jpg';
import trainerMeera from './images/trainer-meera.jpg';
import trainerKaran from './images/trainer-karan.jpg';

const API_BASE = import.meta.env.VITE_API_URL || '';
const BACKEND_URL = import.meta.env.VITE_BACKEND_URL || 'http://127.0.0.1:8000';
const DEMO_IMAGES = ['/images/yoga-flow.png', '/images/yoga-breathe.png', '/images/yoga-hero.png'];

const FALLBACK_DATA = {
  home: {
    featured_course: {
      id: 6,
      slug: 'holistic-wellness-healthy-living',
      title: 'Holistic Wellness & Healthy Living',
      description: 'A practical wellness course covering mindful habits, daily routines, relaxation, movement, and balanced lifestyle practices for healthier everyday living.',
      lessons_count: 0,
      purchase_mode: 'both',
    },
    stats: { courses: 6, lessons: 2, plans: 2 },
  },
  courses: [
    {
      id: 6,
      slug: 'holistic-wellness-healthy-living',
      title: 'Holistic Wellness & Healthy Living',
      description: 'A practical wellness course covering mindful habits, daily routines, relaxation, movement, and balanced lifestyle practices for healthier everyday living.',
      lessons_count: 0,
      purchase_mode: 'both',
      published_at: '2026-09-09T16:25:23+04:00',
    },
    {
      id: 5,
      slug: 'pranayama-breathwork',
      title: 'Pranayama & Breathwork',
      description: 'Explore traditional pranayama and guided breathing exercises designed to improve breath awareness, relaxation, concentration, and overall well-being.',
      lessons_count: 0,
      purchase_mode: 'both',
      published_at: '2026-09-09T16:24:33+04:00',
    },
    {
      id: 4,
      slug: 'meditation-for-inner-peace',
      title: 'Meditation for Inner Peace',
      description: 'Learn practical meditation and breathing techniques to reduce daily stress, improve focus, calm the mind, and develop a peaceful daily routine.',
      lessons_count: 0,
      purchase_mode: 'both',
      published_at: '2026-09-09T16:24:11+04:00',
    },
    {
      id: 3,
      slug: 'beginner-yoga-mindfulness',
      title: 'Beginner Yoga & Mindfulness',
      description: 'A beginner-friendly course designed to improve flexibility, balance, breathing, and mental calm through simple yoga postures and mindfulness practices.',
      lessons_count: 0,
      purchase_mode: 'both',
      published_at: '2026-09-09T16:23:35+04:00',
    },
    {
      id: 2,
      slug: 'yoga-for-beginners-21-day-foundation',
      title: 'Yoga for Beginners',
      description: 'Start your yoga journey with this beginner-friendly 21-day program. Learn basic yoga poses, breathing techniques, flexibility exercises, balance, and relaxation practices.',
      lessons_count: 0,
      purchase_mode: 'both',
      published_at: '2026-09-08T08:46:39+04:00',
    },
    {
      id: 1,
      slug: 'local-demo-course',
      title: 'Local Demo Course',
      description: 'Minimal published course for local QA.',
      lessons_count: 2,
      purchase_mode: 'both',
      published_at: '2026-09-06T08:50:23+04:00',
    },
  ],
  plans: [
    {
      id: 1,
      uuid: '81e94a2c-ef19-4e91-81c3-3a62313b842e',
      name: 'Local Demo Plan',
      slug: 'local-demo-plan',
      short_description: 'QA monthly plan',
      description: 'Local-only subscription used to exercise checkout and access.',
      billing_type: 'monthly',
      duration_days: 30,
      price_aed: 99,
      price_usd: 29,
      courses_count: 1,
      is_featured: true,
    },
    {
      id: 3,
      uuid: '67a8989c-2cc0-4dd8-816e-f37a7f324a36',
      name: 'Soul Starter',
      slug: 'soul-starter',
      short_description: 'Begin your wellness journey with essential yoga, meditation and mindfulness courses.',
      description: '<p></p>',
      billing_type: 'monthly',
      duration_days: null,
      price_aed: 39,
      price_usd: 452,
      courses_count: 0,
      is_featured: true,
    },
  ],
  blog: [
    {
      id: 2,
      slug: '5-simple-yoga-practices-for-a-peaceful-morning',
      title: '5 Simple Yoga Practices for a Peaceful Morning',
      excerpt: 'Start your day with simple yoga practices that help awaken your body, calm your mind, and bring positive energy into your morning.',
      content: '<p></p>',
      featured_image_path: 'https://www.shutterstock.com/image-vector/toogle-slider-260nw-1110907754.jpg',
      category: 'Local Updates',
      author: 'Arjun Dixit',
      reading_time_minutes: 1,
      published_at: '2026-09-09T16:51:38+04:00',
    },
    {
      id: 1,
      slug: 'local-welcome',
      title: 'Local Welcome Post',
      excerpt: 'A published local blog post for QA.',
      content: '<p>This post exists so the public blog index and show pages have content.</p>',
      featured_image_path: null,
      category: 'Local Updates',
      author: 'Super Admin',
      reading_time_minutes: 1,
      published_at: '2026-09-06T08:50:23+04:00',
    },
  ],
};

async function getJson(path) {
  const response = await fetch(`${API_BASE}/api/${path}`, { headers: { Accept: 'application/json' } });
  if (!response.ok) throw new Error(`API ${response.status} on /api/${path}`);
  return response.json();
}

function navigate(to) {
  window.history.pushState({}, '', to);
  window.dispatchEvent(new PopStateEvent('popstate'));
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function usePath() {
  const [path, setPath] = useState(window.location.pathname);
  useEffect(() => {
    const update = () => setPath(window.location.pathname);
    window.addEventListener('popstate', update);
    return () => window.removeEventListener('popstate', update);
  }, []);
  return path;
}

function AppLink({ href, children, className = '', onClick }) {
  return <a className={className} href={href} onClick={(event) => { if (href.startsWith('/')) { event.preventDefault(); navigate(href); } onClick?.(); }}>{children}</a>;
}

function Arrow({ direction = 'right' }) {
  return <span aria-hidden="true" className={`arrow arrow-${direction}`}>↗</span>;
}

function Mark({ small = false }) {
  return <span className={`brand-mark ${small ? 'brand-mark-small' : ''}`} aria-hidden="true"><i /><i /><i /><i /></span>;
}

function Button({ href, children, variant = 'primary', className = '', onClick }) {
  return <AppLink href={href} onClick={onClick} className={`button button-${variant} ${className}`}>{children}<Arrow /></AppLink>;
}

function TiltCard({ children, className = '' }) {
  const [style, setStyle] = useState({});
  return <div className={`tilt-card ${className}`} style={style} onPointerMove={(event) => {
    const rect = event.currentTarget.getBoundingClientRect();
    const x = (event.clientX - rect.left) / rect.width - 0.5;
    const y = (event.clientY - rect.top) / rect.height - 0.5;
    setStyle({ transform: `perspective(900px) rotateX(${y * -5}deg) rotateY(${x * 6}deg) translateY(-4px)` });
  }} onPointerLeave={() => setStyle({})}>{children}</div>;
}

function Header({ menuOpen, setMenuOpen }) {
  const [user, setUser] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem('foursouls_user') || 'null');
    } catch {
      return null;
    }
  });

  useEffect(() => {
    const handleAuth = () => {
      try {
        setUser(JSON.parse(localStorage.getItem('foursouls_user') || 'null'));
      } catch {
        setUser(null);
      }
    };
    window.addEventListener('foursouls_auth_changed', handleAuth);
    window.addEventListener('storage', handleAuth);

    fetch('/api/auth/me', { headers: { Accept: 'application/json' }, credentials: 'include' })
      .then((response) => {
        if (response.ok) return response.json();
        if (response.status === 401) {
          localStorage.removeItem('foursouls_user');
          setUser(null);
        }
        return null;
      })
      .then((data) => {
        if (data?.user) {
          localStorage.setItem('foursouls_user', JSON.stringify(data.user));
          setUser(data.user);
        }
      })
      .catch(() => {});

    return () => {
      window.removeEventListener('foursouls_auth_changed', handleAuth);
      window.removeEventListener('storage', handleAuth);
    };
  }, []);

  const handleLogout = async () => {
    try {
      await fetch('/api/auth/logout', { method: 'POST', credentials: 'include' });
    } catch {}
    localStorage.removeItem('foursouls_user');
    setUser(null);
    window.dispatchEvent(new Event('foursouls_auth_changed'));
    navigate('/');
  };

  const links = [
    ['Why FourSouls', '/about'],
    ['Live Sessions', '/live-sessions'],
    ['Membership', '/plans'],
    ['Journal', '/blog'],
  ];
  return (
    <header className="site-header-replica">
      <div className="section-shell header-shell">
        <AppLink href="/" className="replica-brand" onClick={() => setMenuOpen(false)}>
          <img src={logoImage} alt="FourSouls" className="replica-logo-img" />
        </AppLink>
        <button className="replica-menu-toggle" onClick={() => setMenuOpen(!menuOpen)} aria-label="Toggle navigation">
          <span /><span /><span />
        </button>
        <nav className={`replica-nav ${menuOpen ? 'is-open' : ''}`}>
          {links.map(([label, href]) => (
            <AppLink key={href} href={href} onClick={() => setMenuOpen(false)}>
              {label}
            </AppLink>
          ))}
        </nav>
        <div className="replica-header-actions">
          {user ? (
            <>
              {user.has_active_membership && !user.membership?.is_expired ? (
                <AppLink href="/plans" className="header-plan-badge" onClick={() => setMenuOpen(false)} title="View Active Membership Plan">
                  🪷 {user.membership?.plan_name || 'Active'} Plan
                </AppLink>
              ) : (
                <AppLink href="/plans" className="header-plan-badge header-plan-badge-upgrade" onClick={() => setMenuOpen(false)} title="Choose Membership Plan">
                  ⚡ Choose Plan
                </AppLink>
              )}
              <AppLink href="/live-sessions" className="replica-signin-link" style={{ fontWeight: 650, color: '#e6c887' }} onClick={() => setMenuOpen(false)}>
                🧘 {user.full_name || 'My Studio'}
              </AppLink>
              <button onClick={handleLogout} className="button-replica-cta" style={{ background: '#27272a', border: '1px solid #3f3f46', color: '#fff', cursor: 'pointer' }}>
                Log out
              </button>
            </>
          ) : (
            <>
              <AppLink href="/login?redirect=/plans" className="replica-signin-link" onClick={() => setMenuOpen(false)}>
                Sign in
              </AppLink>
              <AppLink href="/plans" className="button-replica-cta" onClick={() => setMenuOpen(false)}>
                Start Free Trial ➔
              </AppLink>
            </>
          )}
        </div>
      </div>
    </header>
  );
}

function Noise() { return <div className="noise" aria-hidden="true" />; }

function SectionHeading({ eyebrow, title, copy, action }) {
  return <div className="section-heading">
    <div><span className="eyebrow">{eyebrow}</span><h2>{title}</h2></div>
    <div className="section-heading-side">{copy && <p>{copy}</p>}{action}</div>
  </div>;
}

function CourseCard({ course, featured = false, image = DEMO_IMAGES[0] }) {
  const displayCourse = course;
  return <TiltCard className={`course-card ${featured ? 'course-card-featured' : ''}`}>
    <div className="course-visual"><img src={image} alt="Yoga class in a warm studio" /><span className="course-number">0{course.id || '1'}</span><span className="visual-orbit orbit-a" /><span className="visual-orbit orbit-b" /><span className="visual-orbit orbit-c" /><span className="visual-label">FOURSOULS / YOGA STUDIO</span></div>
    <div className="course-card-body"><div className="meta-row"><span>{displayCourse.lessons_count || 0} lessons</span><span>{displayCourse.purchase_mode === 'both' ? 'Flexible access' : 'On demand'}</span></div><h3>{displayCourse.title}</h3><p>{displayCourse.description || 'A considered yoga experience for the curious and committed.'}</p><AppLink href={`/courses/${displayCourse.slug}`} className="text-link">View class <Arrow /></AppLink></div>
  </TiltCard>;
}

function Home({ data }) {
  const featured = data.home?.featured_course || data.courses[0];
  const stats = data.home?.stats || {};
  const courseCount = stats.courses || data.courses.length;
  const lessonCount = stats.lessons || data.courses.reduce((total, course) => total + (course.lessons_count || 0), 0);
  const planCount = stats.plans || data.plans.length;
  return <>
    <section className="hero section-shell">
      <div className="hero-copy reveal"><span className="eyebrow">A modern practice for an ancient rhythm <span className="eyebrow-dot" /></span><h1>Return to your <em>body.</em></h1><p className="hero-lede">FourSouls Yoga is a considered space for building strength, softness, and a practice that stays with you long after class.</p><div className="hero-actions"><Button href="/courses">Explore classes</Button><AppLink href="/about" className="play-link"><span className="play-icon">▶</span> Meet the studio</AppLink></div><div className="hero-proof"><span className="avatar-stack"><i>A</i><i>R</i><i>M</i><i>+</i></span><span><strong>Move at your pace.</strong><small>Join a growing circle of mindful practitioners.</small></span></div></div>
      <div className="hero-art reveal reveal-delay"><div className="hero-glow" /><div className="hero-ring ring-one" /><div className="hero-ring ring-two" /><TiltCard className="floating-card floating-card-main"><div className="floating-top"><span className="live-pulse" /> LIVE / 09:42</div><div className="floating-symbol">✦</div><span className="floating-kicker">TODAY'S PRACTICE</span><strong>Make space to<br />come home.</strong><div className="floating-footer"><span>FOURS OULS / YOGA 001</span><span>↗</span></div></TiltCard><TiltCard className="floating-card floating-card-small"><span className="small-label">MEMBERSHIP</span><strong>01</strong><span>new rhythm</span></TiltCard><div className="art-caption">A quiet place<br />to feel fully here.</div></div>
    </section>
    <section className="marquee"><div className="marquee-track"><span>MOVE SLOWLY</span><i>✦</i><span>BREATHE DEEPLY</span><i>✦</i><span>LIVE FULLY</span><i>✦</i><span>MOVE SLOWLY</span><i>✦</i><span>BREATHE DEEPLY</span><i>✦</i></div></section>
    <section className="section-shell intro-section"><div className="intro-statement"><span className="eyebrow">Why FourSouls Yoga</span><p>Not another class to rush through. <em>A place to return to.</em></p><div className="intro-live-data"><span><strong>{courseCount}</strong><small>courses</small></span><span><strong>{lessonCount}</strong><small>lessons</small></span><span><strong>{planCount}</strong><small>membership plans</small></span></div></div><div className="intro-detail"><p>Yoga should feel less like performing a perfect shape and more like listening well. Our classes bring breath, rhythm, and a little beauty to the work of feeling at home in yourself.</p><p className="intro-featured">Featured practice: <strong>{featured?.title || 'Foundations of Flow'}</strong>{featured?.lessons_count ? ` · ${featured.lessons_count} lessons` : ''}</p><Button href="/about" variant="outline">Our approach</Button></div></section>
    <section className="section-shell courses-section"><SectionHeading eyebrow="The class library" title="Find your next practice." copy={`${courseCount} curated course${courseCount === 1 ? '' : 's'} with ${lessonCount} lessons to explore.`} action={<AppLink href="/courses" className="text-link">View all classes <Arrow /></AppLink>} /><div className="course-grid">{data.courses.map((course, index) => <CourseCard key={course.id || course.slug} course={course} image={DEMO_IMAGES[index % DEMO_IMAGES.length]} featured={course.slug === featured?.slug} />)}</div></section>
    <section className="dark-band"><div className="section-shell dark-band-inner"><div><span className="eyebrow eyebrow-light">A different kind of progress</span><h2>More breath.<br /><em>Less noise.</em></h2></div><div className="dark-copy"><p>Every FourSouls class is designed to help you slow down, notice more, and carry a little steadiness into the rest of your day.</p><Button href="/courses" variant="light">Browse the classes</Button></div></div></section>
    <section className="section-shell plans-section"><SectionHeading eyebrow="Choose your rhythm" title="A membership that meets you there." copy={`${planCount} membership option${planCount === 1 ? '' : 's'} with access to your practice at your pace.`} action={<AppLink href="/plans" className="text-link">Compare plans <Arrow /></AppLink>} /><div className="membership-overview"><span><strong>{planCount}</strong> membership option{planCount === 1 ? '' : 's'}</span><span><strong>{data.plans.reduce((total, plan) => total + (plan.courses_count || 0), 0)}</strong> curated courses included</span><span><strong>{data.plans.some((plan) => plan.billing_type === 'lifetime') ? 'Flexible' : 'Monthly'}</strong> access</span></div><div className="plan-grid">{data.plans.map((plan, index) => <PlanCard key={plan.id || plan.slug} plan={plan} featured={plan.is_featured || index === 0} />)}</div></section>
    <section className="section-shell journal-section"><SectionHeading eyebrow="From the journal" title="Notes for the road." action={<AppLink href="/blog" className="text-link">Read the journal <Arrow /></AppLink>} /><div className="journal-grid">{data.blog.slice(0, 3).map((post, index) => <BlogCard key={post.id || post.slug} post={post} index={index} />)}</div></section>
    <CTA />
  </>;
}

// HomeBanner is imported from ./HomeBanner.jsx

function PlanCard({ plan, featured = false }) {
  return <TiltCard className={`plan-card ${featured ? 'plan-card-featured' : ''}`}><div className="plan-card-top"><span className="plan-dot" />{featured && <span className="featured-pill">Most loved</span>}<span className="plan-type">{plan.billing_type || 'monthly'}</span></div><h3>{plan.name}</h3><p>{plan.short_description}</p><div className="plan-price"><strong><small>AED</small>{Number(plan.price_aed || 0).toFixed(0)}</strong><span>/ {plan.billing_type === 'lifetime' ? 'forever' : 'month'}</span></div><div className="plan-rule" /><div className="plan-includes"><span>✦ {plan.courses_count || 1} curated course{plan.courses_count === 1 ? '' : 's'}</span><span>✦ Community & live sessions</span></div><Button href="/plans" variant={featured ? 'primary' : 'outline'}>See membership</Button></TiltCard>;
}

function formatBlogDate(value) {
  if (!value) return 'Fresh note';
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit', year: 'numeric' }).format(new Date(value));
}

function BlogCard({ post, index = 0 }) {
  const image = post.featured_image_path || DEMO_IMAGES[(index + 2) % DEMO_IMAGES.length];
  const category = post.category || ['Yoga', 'Lifestyle', 'Mindfulness'][index] || 'Field notes';

  return <AppLink href={`/blog/${post.slug}`} className={`blog-card blog-card-${index}`}>
    <div className="blog-card-media">
      <img src={image} alt="" />
      <div className="blog-card-overlay" />
      <div className="blog-card-top"><span>{String(index + 1).padStart(2, '0')}</span><span>{post.reading_time_minutes || 4} MIN READ</span></div>
      <div className="blog-card-content">
        <span className="blog-card-category">{category}</span>
        <h3>{post.title}</h3>
        <p>{post.excerpt}</p>
        <div className="blog-card-footer">
          <div className="blog-author"><img src="/images/yoga-breathe.png" alt="" /><span><strong>By {post.author || 'Four Soul'}</strong><small>{formatBlogDate(post.published_at)}</small></span></div>
          <span className="blog-read-more">Read more <i aria-hidden="true">→</i></span>
        </div>
      </div>
    </div>
  </AppLink>;
}

function Courses({ courses }) { return <PageIntro eyebrow="The curriculum" title={<>A little more <em>curious.</em></>} copy="Thoughtful courses, practical rituals, and ideas with enough room to breathe." dark><div className="catalog-grid">{courses.map((course) => <CourseCard key={course.id || course.slug} course={course} />)}</div></PageIntro>; }

function CourseDetail({ course }) { return <PageIntro eyebrow="Course / local demo" title={<>{course?.title || 'A beautiful beginning'}<em>.</em></>} copy={course?.description || 'A considered learning path for your next chapter.'}><div className="detail-layout"><div className="detail-visual"><span className="course-number">01</span><span className="visual-orbit orbit-a" /><span className="visual-orbit orbit-b" /><span className="visual-orbit orbit-c" /><span className="visual-label">FOURS OULS / STUDIO</span></div><div className="detail-copy"><span className="eyebrow">Inside the experience</span><h2>Make progress feel like a place you want to be.</h2><p>Move through clear, compact lessons designed for momentum. Return to the ideas that spark something, and take the ones that stick into your work.</p><div className="detail-stats"><span><strong>{course?.lessons_count || 2}</strong><small>lessons</small></span><span><strong>∞</strong><small>your pace</small></span><span><strong>01</strong><small>new rhythm</small></span></div><div className="detail-actions"><a className="button button-primary" href={`${BACKEND_URL}/learn/courses/${course?.slug || ""}`}>Start learning <Arrow /></a><a className="button button-outline" href={`${BACKEND_URL}/plans`}>View access plans <Arrow /></a></div></div></div></PageIntro>; }

function Plans() { return <MembershipPage />; }

function Journal({ posts }) { return <PageIntro eyebrow="The journal" title={<>Notes for the <em>road.</em></>} copy="Ideas on learning, attention, and the art of making a life that feels like yours."><div className="journal-page-grid">{posts.map((post, index) => <BlogCard key={post.id || post.slug} post={post} index={index} />)}</div></PageIntro>; }

function BlogDetail({ post }) { return <PageIntro eyebrow={post?.category || 'Field notes'} title={<>{post?.title || 'A softer way to become excellent'}<em>.</em></>} copy={post?.excerpt || 'A note from the FourSouls studio.'}><article className="article"><div className="article-art"><span>FOURS OULS<br />JOURNAL</span><strong>✦</strong></div><div className="article-body"><div dangerouslySetInnerHTML={{ __html: post?.content || '<p>There is a version of learning that does not ask you to sprint. It asks you to return, pay attention, and make something real with what you find.</p><p>That is the rhythm we are interested in here: useful beauty, honest practice, and enough space for the next good question.</p>' }} /><Button href="/blog" variant="outline">Back to journal</Button></div></article></PageIntro>; }

async function getFrontendCsrfToken() {
  try {
    const response = await fetch('/api/auth/csrf', { credentials: 'include', headers: { Accept: 'application/json' } });
    if (!response.ok) return '';
    const body = await response.json().catch(() => ({}));
    return body.token || '';
  } catch (e) {
    return '';
  }
}

async function submitCustomerAuth(path, payload) {
  const token = await getFrontendCsrfToken();
  const response = await fetch(`/api/auth/${path}`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { 'X-CSRF-TOKEN': token, 'X-XSRF-TOKEN': token } : {}),
    },
    body: JSON.stringify(payload),
  });
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    const firstError = Object.values(body.errors || {})[0];
    throw new Error(Array.isArray(firstError) ? firstError[0] : (body.message || 'Please check your credentials and try again.'));
  }
  return body;
}

function AuthVisual({ register = false }) {
  return <div className="login-visual"><img src="/images/yoga-banner.png" alt="Yoga practice in a sunlit studio" /><div className="login-visual-copy"><span className="eyebrow eyebrow-light">FOURSOULS YOGA</span><h1>{register ? <>Begin your<br /><em>new rhythm.</em></> : <>Come back to<br /><em>your rhythm.</em></>}</h1><p>Guided practice, thoughtful classes, and a calmer way to keep showing up.</p><div className="login-quote">“The practice meets you exactly where you are.”</div></div></div>;
}

function CustomerLoginProper() {
  const [form, setForm] = useState({ email: '', password: '', remember: false });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  async function submit(event) {
    event.preventDefault(); setError(''); setLoading(true);
    try {
      const data = await submitCustomerAuth('login', form);
      if (data?.user) {
        localStorage.setItem('foursouls_user', JSON.stringify(data.user));
        window.dispatchEvent(new Event('foursouls_auth_changed'));
      }
      const params = new URLSearchParams(window.location.search);
      const redirectTarget = params.get('redirect') || '/plans';
      navigate(redirectTarget);
    } catch (requestError) {
      setError(requestError.message);
    } finally {
      setLoading(false);
    }
  }
  return <main className="login-page"><AuthVisual /><div className="login-panel"><AppLink href="/" className="wordmark"><Mark /><span>FOUR<span>SOULS YOGA</span></span></AppLink><div className="login-heading"><span className="eyebrow">Member portal</span><h2>Welcome <em>back.</em></h2><p>Sign in to continue your practice.</p></div>{error && <div className="login-error" role="alert">{error}</div>}<form className="login-form" onSubmit={submit}><label>Email address<input type="email" value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} autoComplete="email" required /></label><label>Password<input type="password" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} autoComplete="current-password" required /></label><div className="login-options"><label className="remember-option"><input type="checkbox" checked={form.remember} onChange={(event) => setForm({ ...form, remember: event.target.checked })} /> Remember me</label><a href={`${BACKEND_URL}/forgot-password`}>Forgot password?</a></div><button className="login-submit" type="submit" disabled={loading}>{loading ? 'Signing in…' : 'Sign in'} <span>↗</span></button></form><div className="login-divider"><span>New to FourSouls?</span></div><AppLink className="login-register" href="/register">Create your account <span>↗</span></AppLink><p className="login-note">By continuing, you agree to our studio terms and privacy policy.</p></div></main>;
}

function CustomerRegisterProper() {
  const [form, setForm] = useState({ full_name: '', email: '', mobile_country_code: '+971', mobile_number: '', preferred_language: 'en', password: '', password_confirmation: '', marketing_consent: '', terms_acceptance: false, privacy_acceptance: false });
  const [error, setError] = useState(''); const [loading, setLoading] = useState(false);
  const update = (name, value) => setForm((current) => ({ ...current, [name]: value }));
  async function submit(event) {
    event.preventDefault(); setError(''); setLoading(true);
    try {
      const data = await submitCustomerAuth('register', {
        ...form,
        password_confirmation: form.password_confirmation || form.password,
        marketing_consent: form.marketing_consent === '1',
      });
      if (data?.user) {
        localStorage.setItem('foursouls_user', JSON.stringify(data.user));
        window.dispatchEvent(new Event('foursouls_auth_changed'));
      }
      const params = new URLSearchParams(window.location.search);
      const redirectTarget = params.get('redirect') || '/plans';
      navigate(redirectTarget);
    } catch (requestError) {
      setError(requestError.message);
    } finally {
      setLoading(false);
    }
  }
  return <main className="login-page"><AuthVisual register /><div className="login-panel"><AppLink href="/" className="wordmark"><Mark /><span>FOUR<span>SOULS YOGA</span></span></AppLink><div className="login-heading"><span className="eyebrow">Member portal</span><h2>Start a <em>practice.</em></h2><p>Create your account and make room for a steadier rhythm.</p></div>{error && <div className="login-error" role="alert">{error}</div>}<form className="login-form register-form" onSubmit={submit}><label>Full name<input type="text" value={form.full_name} onChange={(event) => update('full_name', event.target.value)} autoComplete="name" required /></label><label>Email address<input type="email" value={form.email} onChange={(event) => update('email', event.target.value)} autoComplete="email" required /></label><label>Mobile number<div className="mobile-fields"><select value={form.mobile_country_code} onChange={(event) => update('mobile_country_code', event.target.value)} aria-label="Country code"><option value="+971">UAE (+971)</option><option value="+91">India (+91)</option><option value="+1">United States (+1)</option><option value="+44">United Kingdom (+44)</option></select><input type="tel" value={form.mobile_number} onChange={(event) => update('mobile_number', event.target.value)} inputMode="numeric" autoComplete="tel-national" placeholder="501234567" required /></div></label><label>Password<input type="password" value={form.password} onChange={(event) => update('password', event.target.value)} autoComplete="new-password" required /></label><label>Confirm password<input type="password" value={form.password_confirmation} onChange={(event) => update('password_confirmation', event.target.value)} autoComplete="new-password" required /></label><label className="consent-option"><input type="checkbox" checked={form.terms_acceptance} onChange={(event) => update('terms_acceptance', event.target.checked)} required /> I accept the <a href={`${BACKEND_URL}/terms`} target="_blank" rel="noreferrer">Terms</a>.</label><label className="consent-option"><input type="checkbox" checked={form.privacy_acceptance} onChange={(event) => update('privacy_acceptance', event.target.checked)} required /> I accept the <a href={`${BACKEND_URL}/privacy`} target="_blank" rel="noreferrer">Privacy Policy</a>.</label><fieldset className="consent-group"><legend>Marketing emails</legend><label className="consent-option"><input type="radio" name="marketing_consent" value="1" checked={form.marketing_consent === '1'} onChange={(event) => update('marketing_consent', event.target.value)} required /> Yes</label><label className="consent-option"><input type="radio" name="marketing_consent" value="0" checked={form.marketing_consent === '0'} onChange={(event) => update('marketing_consent', event.target.value)} required /> No</label></fieldset><button className="login-submit" type="submit" disabled={loading}>{loading ? 'Creating account…' : 'Create account'} <span>↗</span></button></form><div className="login-divider"><span>Already a member?</span></div><AppLink className="login-register" href="/login">Sign in <span>↗</span></AppLink></div></main>;
}

function MemberPortal() {
  return <PageIntro eyebrow="Member portal" title={<>Your practice is <em>waiting.</em></>} copy="Your customer account is connected. Continue exploring the FourSouls studio."><div className="detail-actions"><Button href="/courses">Explore classes</Button><a className="button button-outline" href={`${BACKEND_URL}/learn`}>Open My learning <Arrow /></a></div></PageIntro>;
}

function CustomerLogin() {
  return <main className="login-page">
    <div className="login-visual"><img src="/images/yoga-banner.png" alt="Yoga practice in a sunlit studio" /><div className="login-visual-copy"><span className="eyebrow eyebrow-light">FOURSOULS YOGA</span><h1>Come back to<br /><em>your rhythm.</em></h1><p>Guided practice, thoughtful classes, and a calmer way to keep showing up.</p><div className="login-quote">“The practice meets you exactly where you are.”</div></div></div>
    <div className="login-panel"><AppLink href="/" className="wordmark"><Mark /><span>FOUR<span>SOULS YOGA</span></span></AppLink><div className="login-heading"><span className="eyebrow">Member portal</span><h2>Welcome <em>back.</em></h2><p>Sign in to continue your practice.</p></div><div className="login-form"><div className="login-success" role="status">Sign in securely to access your courses, progress, assignments, quizzes, live sessions, and certificates.</div><a className="login-submit" href={`${BACKEND_URL}/login`}>Continue to secure sign in <span>↗</span></a></div><div className="login-divider"><span>New to FourSouls?</span></div><a className="login-register" href={`${BACKEND_URL}/register`}>Create your account <span>↗</span></a><p className="login-note">By continuing, you agree to our studio terms and privacy policy.</p></div>
  </main>;
}

function About() { return <AboutUsPage />; }

function PageIntro({ eyebrow, title, copy, children, dark = false }) { return <main className={`inner-page ${dark ? 'inner-page-dark' : ''}`}><section className="section-shell page-hero"><span className="eyebrow">{eyebrow}</span><h1>{title}</h1><p className="page-lede">{copy}</p></section><section className="section-shell page-content">{children}</section><CTA /></main>; }

function CTA() { return <section className="section-shell cta-section"><div className="cta-orbit"><span /><span /><span /></div><div className="cta-content"><span className="eyebrow eyebrow-light">Your next chapter is already moving.</span><h2>Make something<br /><em>meaningful.</em></h2><Button href="/courses" variant="light">Find your starting point</Button></div></section>; }

const SOCIAL_LINKS = [
  {
    name: 'LinkedIn',
    href: 'https://linkedin.com',
    svg: (
      <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
        <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
      </svg>
    ),
  },
  {
    name: 'X',
    href: 'https://x.com',
    svg: (
      <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor">
        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
      </svg>
    ),
  },
  {
    name: 'Facebook',
    href: 'https://facebook.com',
    svg: (
      <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
        <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95C18.05 21.45 22 17.19 22 12z"/>
      </svg>
    ),
  },
  {
    name: 'Instagram',
    href: 'https://instagram.com',
    svg: (
      <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
      </svg>
    ),
  },
  {
    name: 'YouTube',
    href: 'https://youtube.com',
    svg: (
      <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
      </svg>
    ),
  },
];

function Footer() {
  return (
    <footer className="replica-footer">
      <div className="section-shell">
        {/* MOBILE VIEW (Exact layout of user sample image with FourSouls theme & data) */}
        <div className="mobile-sample-footer">
          <div className="mobile-footer-brand-wrap">
            <div className="mobile-footer-logo-card">
              <img src={logoImage} alt="FourSouls" className="footer-logo-img" />
            </div>
            <p className="mobile-footer-bio">
              A sanctuary for holistic wellness, transformative yoga, and conscious mindful living — nurturing mind, body, and spirit.
            </p>
            <div className="mobile-footer-social-row">
              {SOCIAL_LINKS.map((link) => (
                <a
                  key={link.name}
                  href={link.href}
                  target="_blank"
                  rel="noreferrer"
                  aria-label={link.name}
                  className="mobile-social-badge"
                >
                  {link.svg}
                </a>
              ))}
            </div>
          </div>

          {/* Quick Links Box matching sample image */}
          <div className="mobile-footer-links-box">
            <div className="mobile-links-heading-wrap">
              <span className="mobile-links-title">QUICK LINKS</span>
              <div className="mobile-links-gold-bar" />
            </div>
            <div className="mobile-links-pill-list">
              <AppLink href="/" className="mobile-pill-btn">
                <span>Home</span>
                <span className="mobile-pill-chevron">›</span>
              </AppLink>
              <AppLink href="/about" className="mobile-pill-btn">
                <span>About Us</span>
                <span className="mobile-pill-chevron">›</span>
              </AppLink>
              <AppLink href="/#live-sessions" className="mobile-pill-btn">
                <span>Live Classes</span>
                <span className="mobile-pill-chevron">›</span>
              </AppLink>
              <AppLink href="/plans" className="mobile-pill-btn">
                <span>Membership Plans</span>
                <span className="mobile-pill-chevron">›</span>
              </AppLink>
              <AppLink href="/courses" className="mobile-pill-btn">
                <span>Courses</span>
                <span className="mobile-pill-chevron">›</span>
              </AppLink>
              <AppLink href="/blog" className="mobile-pill-btn">
                <span>Journal</span>
                <span className="mobile-pill-chevron">›</span>
              </AppLink>
              <AppLink href="/about" className="mobile-pill-btn">
                <span>Contact Us</span>
                <span className="mobile-pill-chevron">›</span>
              </AppLink>
            </div>
          </div>

          {/* Support & Legal strip on mobile */}
          <div className="mobile-footer-sub-card">
            <div className="mobile-sub-links-row">
              <AppLink href="/about">FAQ</AppLink>
              <span>·</span>
              <AppLink href="/about">Terms & Conditions</AppLink>
              <span>·</span>
              <AppLink href="/about">Privacy</AppLink>
              <span>·</span>
              <AppLink href="/about">Refunds</AppLink>
            </div>
            <div className="mobile-newsletter-wrap">
              <p className="mobile-newsletter-text">Join Our Newsletter</p>
              <form className="footer-newsletter-form" onSubmit={(e) => { e.preventDefault(); alert('Thank you for subscribing to FourSouls!'); }}>
                <input type="email" placeholder="Enter your email" required />
                <button type="submit" aria-label="Subscribe">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
              </form>
            </div>
          </div>
        </div>

        {/* DESKTOP / LAPTOP VIEW */}
        <div className="desktop-footer-grid">
          <div className="footer-col footer-col-brand">
            <div className="footer-logo-card">
              <img src={logoImage} alt="FourSouls" className="footer-logo-img" />
            </div>
            <p className="footer-bio">
              A sanctuary for holistic wellness, transformative yoga, and conscious mindful living — nurturing mind, body, and spirit.
            </p>
            <div className="footer-social-row">
              {SOCIAL_LINKS.map((link) => (
                <a
                  key={link.name}
                  href={link.href}
                  target="_blank"
                  rel="noreferrer"
                  aria-label={link.name}
                  className="social-pill-letter"
                >
                  {link.svg}
                </a>
              ))}
            </div>
          </div>
          <div className="footer-col">
            <h4>Quick Links</h4>
            <AppLink href="/about">Why FourSouls</AppLink>
            <AppLink href="/plans">Membership Plans</AppLink>
            <AppLink href="/#live-sessions">Live Sessions</AppLink>
            <AppLink href="/blog">Journal</AppLink>
          </div>
          <div className="footer-col">
            <h4>Support</h4>
            <AppLink href="/about">Help Center</AppLink>
            <AppLink href="/about">Contact Us</AppLink>
            <AppLink href="/#faq">FAQ</AppLink>
            <AppLink href="/about">Terms & Conditions</AppLink>
          </div>
          <div className="footer-col">
            <h4>More</h4>
            <AppLink href="/plans">Membership</AppLink>
            <AppLink href="/blog">Journal</AppLink>
            <AppLink href="/about">Privacy Policy</AppLink>
            <AppLink href="/about">Refund Policy</AppLink>
          </div>
          <div className="footer-col footer-col-newsletter">
            <h4>Join Our Newsletter</h4>
            <p>Get the latest updates and wellness tips.</p>
            <form className="footer-newsletter-form" onSubmit={(e) => { e.preventDefault(); alert('Thank you for subscribing to FourSouls!'); }}>
              <input type="email" placeholder="Enter your email" required />
              <button type="submit" aria-label="Subscribe">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
              </button>
            </form>
          </div>
        </div>

        <div className="replica-footer-bottom">
          <div className="bottom-left">
            <span>© 2026 FourSouls. All rights reserved.</span>
          </div>
          <div className="bottom-right">
            <span>Designed with care for mindful living ✦</span>
          </div>
        </div>
      </div>
    </footer>
  );
}

const ADMIN_URL = import.meta.env.VITE_ADMIN_URL || 'http://127.0.0.1:8000/admin';

const ADMIN_GROUPS = [
  { label: 'Workspace', items: [{ label: 'Dashboard', path: '', icon: 'grid' }, { label: 'Analytics Dashboard', path: '/analytics', icon: 'chart' }] },
  { label: 'Assessments', items: [
    { label: 'Assignments', path: '/assignments', icon: 'assignment', description: 'Manage coursework and submissions' },
    { label: 'Certificate Templates', path: '/certificate-templates', icon: 'certificate', description: 'Design and issue credentials' },
    { label: 'Questions', path: '/questions', icon: 'question', description: 'Build your question bank' },
    { label: 'Quizzes', path: '/quizzes', icon: 'quiz', description: 'Configure tests and scoring' },
  ] },
  { label: 'Subscriptions', items: [{ label: 'Subscription Plans', path: '/subscription-plans', icon: 'card', description: 'Pricing and access rules' }] },
  { label: 'Learning', items: [
    { label: 'Courses', path: '/courses', icon: 'book', description: 'Curriculum, sections and lessons' },
    { label: 'Member Content', path: '/member-content', icon: 'video', description: 'Manage member links and videos' },
  ] },
  { label: 'CMS', items: [
    { label: 'Media', path: '/media', icon: 'image', description: 'Images, files and folders' },
    { label: 'Pages', path: '/pages', icon: 'page', description: 'Manage published site pages' },
    { label: 'Blog Posts', path: '/blog-posts', icon: 'pen', description: 'Write and publish journal posts' },
  ] },
];

function AdminIcon({ name }) {
  const paths = {
    grid: <><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></>,
    chart: <><path d="M4 19V5M4 19h17" /><path d="m7 15 4-5 3 2 5-7" /></>,
    assignment: <><rect x="5" y="3" width="14" height="18" rx="2" /><path d="M9 7h6M8 11h8M8 15h5" /></>,
    certificate: <><circle cx="12" cy="9" r="5" /><path d="m9.5 13.5-1 7 3.5-2 3.5 2-1-7" /></>,
    question: <><circle cx="12" cy="12" r="9" /><path d="M9.8 9a2.35 2.35 0 1 1 3.8 1.85c-1 .72-1.6 1.1-1.6 2.65M12 17h.01" /></>,
    quiz: <><path d="M7 3h10v4H7zM5 7h14v14H5z" /><path d="m8 14 2 2 5-5" /></>,
    card: <><rect x="3" y="5" width="18" height="14" rx="2" /><path d="M3 10h18M7 15h4" /></>,
    book: <><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z" /><path d="M4 5.5v15" /></>,
    video: <><rect x="3" y="5" width="13" height="14" rx="2" /><path d="m16 10 5-3v10l-5-3z" /></>,
    image: <><rect x="3" y="4" width="18" height="16" rx="2" /><circle cx="8.5" cy="9" r="1.5" /><path d="m4 17 5-5 3 3 2-2 7 6" /></>,
    page: <><path d="M6 3h9l4 4v14H6z" /><path d="M15 3v5h4M9 13h6M9 17h6" /></>,
    pen: <><path d="m4 20 4.2-1 10.5-10.5a2.1 2.1 0 0 0-3-3L5.2 16z" /><path d="m14 7 3 3" /></>,
    arrow: <><path d="M5 12h14M13 6l6 6-6 6" /></>,
    external: <><path d="M14 4h6v6M20 4l-9 9" /><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" /></>,
  };
  return <svg className="admin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{paths[name] || paths.page}</svg>;
}

function AdminExternalLink({ item, className = '', children }) {
  return <a className={className} href={`${ADMIN_URL}${item.path}`}>{children}</a>;
}

function AdminSidebar({ open, onClose }) {
  return <aside className={`admin-sidebar ${open ? 'is-open' : ''}`}>
    <div className="admin-sidebar-head"><a href="/admin" className="admin-brand"><Mark small /><span>FOURSOULS<small>ADMIN STUDIO</small></span></a><button className="admin-close" onClick={onClose} aria-label="Close menu">×</button></div>
    <nav className="admin-nav" aria-label="Admin navigation">{ADMIN_GROUPS.map((group) => <div className="admin-nav-group" key={group.label}><span className="admin-nav-label">{group.label}</span>{group.items.map((item) => <AdminExternalLink key={item.path} item={item} className="admin-nav-item"><AdminIcon name={item.icon} /><span>{item.label}</span>{item.path === '' && <i className="admin-active-dot" />}</AdminExternalLink>)}</div>)}</nav>
    <div className="admin-sidebar-foot"><a href={ADMIN_URL}><span className="admin-avatar">A</span><span><strong>Administration</strong><small>Open secure backend</small></span><AdminIcon name="external" /></a></div>
  </aside>;
}

function AdminStat({ label, value, note, icon, tone = '' }) {
  return <div className={`admin-stat ${tone}`}><div className="admin-stat-top"><span>{label}</span><span className="admin-stat-icon"><AdminIcon name={icon} /></span></div><strong>{value}</strong><small>{note}</small></div>;
}

function AdminModuleCard({ item }) {
  return <AdminExternalLink item={item} className="admin-module-card"><span className="admin-module-icon"><AdminIcon name={item.icon} /></span><span className="admin-module-copy"><strong>{item.label}</strong><small>{item.description}</small></span><span className="admin-module-arrow"><AdminIcon name="arrow" /></span></AdminExternalLink>;
}

function AdminPortal({ data }) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [query, setQuery] = useState('');
  const courses = data?.courses || [];
  const plans = data?.plans || [];
  const posts = data?.blog || [];
  const modules = ADMIN_GROUPS.slice(1).flatMap((group) => group.items);
  const filteredModules = modules.filter((item) => item.label.toLowerCase().includes(query.toLowerCase()) || item.description?.toLowerCase().includes(query.toLowerCase()));
  const lessonCount = courses.reduce((total, course) => total + Number(course.lessons_count || 0), 0);

  return <div className="admin-shell"><AdminSidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} />{sidebarOpen && <button className="admin-scrim" onClick={() => setSidebarOpen(false)} aria-label="Close navigation" />}<main className="admin-main">
    <header className="admin-topbar"><button className="admin-menu" onClick={() => setSidebarOpen(true)} aria-label="Open menu"><span /><span /><span /></button><div className="admin-breadcrumb"><span>Workspace</span><b>/</b><strong>Dashboard</strong></div><div className="admin-top-actions"><span className="admin-live-status"><i /> Backend connected</span><a href="/" className="admin-site-link">View website <AdminIcon name="external" /></a></div></header>
    <div className="admin-content"><section className="admin-welcome"><div><span className="eyebrow">FourSouls / Administration</span><h1>Good morning, <em>studio.</em></h1><p>Keep the learning experience clear, current, and ready for your community.</p></div><a className="admin-primary-action" href={`${ADMIN_URL}/courses/create`}>Create course <AdminIcon name="arrow" /></a></section>
      <section className="admin-stats" aria-label="Studio overview"><AdminStat label="Published courses" value={courses.length} note={`${lessonCount} lessons in the library`} icon="book" tone="mint" /><AdminStat label="Membership plans" value={plans.length} note="Pricing options available" icon="card" tone="yellow" /><AdminStat label="Journal posts" value={posts.length} note="Published and ready to read" icon="pen" tone="violet" /><AdminStat label="Admin modules" value={modules.length} note="All systems available" icon="grid" tone="orange" /></section>
      <section className="admin-dashboard-grid"><div className="admin-panel admin-activity-panel"><div className="admin-panel-head"><div><span className="eyebrow">Live overview</span><h2>Studio at a glance</h2></div><span className="admin-panel-badge"><i /> Synced with Laravel</span></div><div className="admin-health"><div className="admin-health-mark"><span /><span /><span /></div><div><strong>Everything is in rhythm.</strong><p>Your public catalogue is connected to the administration backend and ready for updates.</p></div></div><div className="admin-mini-list"><div><span>Course catalogue</span><strong>{courses.length ? 'Published' : 'Ready for content'}</strong></div><div><span>Membership access</span><strong>{plans.length ? 'Configured' : 'Needs a plan'}</strong></div><div><span>Journal</span><strong>{posts.length ? 'Publishing' : 'Ready for your first post'}</strong></div></div></div><div className="admin-panel admin-actions-panel"><div className="admin-panel-head"><div><span className="eyebrow">Shortcuts</span><h2>Make a move</h2></div></div><div className="admin-quick-actions"><a href={`${ADMIN_URL}/courses/create`}><AdminIcon name="book" /><span><strong>New course</strong><small>Add sections and lessons</small></span><AdminIcon name="arrow" /></a><a href={`${ADMIN_URL}/assignments/create`}><AdminIcon name="assignment" /><span><strong>New assignment</strong><small>Create learner coursework</small></span><AdminIcon name="arrow" /></a><a href={`${ADMIN_URL}/member-content/create`}><AdminIcon name="video" /><span><strong>Add member content</strong><small>Publish a link or video</small></span><AdminIcon name="arrow" /></a><a href={`${ADMIN_URL}/blog-posts/create`}><AdminIcon name="pen" /><span><strong>Write a post</strong><small>Share a note with members</small></span><AdminIcon name="arrow" /></a></div></div></section>
      <section className="admin-workspace-section"><div className="admin-section-heading"><div><span className="eyebrow">All systems</span><h2>Your studio workspace</h2><p>Every backend area is available here. Select a module to open its secure manager.</p></div><label className="admin-search"><span>⌕</span><input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search modules" aria-label="Search admin modules" /></label></div><div className="admin-modules-grid">{filteredModules.map((item) => <AdminModuleCard key={item.path} item={item} />)}{!filteredModules.length && <div className="admin-empty">No module matches “{query}”.</div>}</div></section>
      <section className="admin-lower-grid"><div className="admin-panel admin-list-panel"><div className="admin-panel-head"><div><span className="eyebrow">Latest curriculum</span><h2>Courses</h2></div><a href={`${ADMIN_URL}/courses`}>Manage all <AdminIcon name="arrow" /></a></div>{courses.slice(0, 4).map((course) => <a className="admin-row" href={`${ADMIN_URL}/courses/${course.id}/edit`} key={course.id}><span className="admin-row-number">{String(course.id).padStart(2, '0')}</span><span><strong>{course.title}</strong><small>{course.lessons_count || 0} lessons · {course.purchase_mode || 'on demand'}</small></span><b>→</b></a>)}{!courses.length && <p className="admin-empty">No courses are published yet. Start with the course manager.</p>}</div><div className="admin-panel admin-list-panel"><div className="admin-panel-head"><div><span className="eyebrow">Latest writing</span><h2>Journal</h2></div><a href={`${ADMIN_URL}/blog-posts`}>Manage all <AdminIcon name="arrow" /></a></div>{posts.slice(0, 4).map((post, index) => <a className="admin-row" href={`${ADMIN_URL}/blog-posts/${post.id}/edit`} key={post.id}><span className={`admin-post-dot post-dot-${index % 3}`} /><span><strong>{post.title}</strong><small>{post.category || 'Field notes'} · {formatBlogDate(post.published_at)}</small></span><b>→</b></a>)}{!posts.length && <p className="admin-empty">No journal posts are published yet. Share the first note.</p>}</div></section>
    </div></main></div>;
}

function DataState({ error, onRetry }) {
  return <main className="inner-page"><section className="section-shell page-hero"><span className="eyebrow">FourSouls / connection</span><h1>{error ? 'The studio is taking a breath.' : 'Loading the studio.'}</h1><p className="page-lede">{error || 'Loading the latest courses, plans, and journal entries from Laravel.'}</p>{error && <button className="button button-primary" type="button" onClick={onRetry}>Try again <Arrow /></button>}</section></main>;
}

function App() {
  const path = usePath();
  const [menuOpen, setMenuOpen] = useState(false);
  const [data, setData] = useState(FALLBACK_DATA);
  const [loadError, setLoadError] = useState('');
  useEffect(() => {
    let cancelled = false;
    Promise.all([
      getJson('home').catch(() => null),
      getJson('courses').catch(() => null),
      getJson('plans').catch(() => null),
      getJson('blog').catch(() => null),
    ])
      .then(([home, courses, plans, blog]) => {
        if (!cancelled) {
          setData((prev) => ({
            home: home || prev?.home || FALLBACK_DATA.home,
            courses: courses && courses.length ? courses : (prev?.courses || FALLBACK_DATA.courses),
            plans: plans && plans.length ? plans : (prev?.plans || FALLBACK_DATA.plans),
            blog: blog && blog.length ? blog : (prev?.blog || FALLBACK_DATA.blog),
          }));
        }
      })
      .catch((error) => {
        if (!cancelled && !data) setLoadError(`Could not load Laravel data. ${error.message}`);
      });
    return () => { cancelled = true; };
  }, []);
  const cleanPath = path.toLowerCase().replace(/\/+$/, '') || '/';
  const slug = cleanPath.split('/').filter(Boolean).pop();
  const page = useMemo(() => {
    if (!data) return null;
    if (cleanPath === '/') return <HomeBanner data={data} />;
    if (cleanPath === '/courses') return <Courses courses={data.courses} />;
    if (cleanPath.startsWith('/courses/')) return <CourseDetail course={data.courses.find((item) => item.slug === slug) || data.courses[0]} />;
    if (cleanPath === '/plans' || cleanPath === '/membership' || cleanPath === '/pricing') return <Plans plans={data.plans} />;
    if (cleanPath === '/live-sessions' || cleanPath === '/live' || cleanPath === '/#live-sessions') return <LiveSessionsPage />;
    if (cleanPath === '/blog' || cleanPath === '/journal') return <JournalPage />;
    if (cleanPath.startsWith('/blog/') || cleanPath.startsWith('/journal/')) return <JournalPage activeSlug={slug} />;
    if (cleanPath === '/login' || cleanPath === '/customer/login') return <CustomerLoginProper />;
    if (cleanPath === '/register') return <CustomerRegisterProper />;
    if (cleanPath === '/member') return <MemberPortal />;
    if (cleanPath === '/about' || cleanPath === '/about-us' || cleanPath === '/why-foursouls') return <About />;
    return <PageIntro eyebrow="404 / lost signal" title={<>This page took a <em>different orbit.</em></>} copy="Let's get you back to something useful."><Button href="/">Return home</Button></PageIntro>;
  }, [data, cleanPath, slug]);
  if (!data && loadError) return <><Noise /><DataState error={loadError} onRetry={() => { setData(FALLBACK_DATA); }} /><Footer /></>;
  return <><Noise /><Header menuOpen={menuOpen} setMenuOpen={setMenuOpen} overlay={path === '/'} />{page}<Footer /></>;
}

const rootElement = document.getElementById('root');
const root = globalThis.__FOURSOULS_REACT_ROOT__ || createRoot(rootElement);
globalThis.__FOURSOULS_REACT_ROOT__ = root;
root.render(<StrictMode><App /></StrictMode>);
