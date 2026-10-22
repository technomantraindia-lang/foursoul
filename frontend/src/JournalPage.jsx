import React, { useState, useMemo, useEffect } from 'react';
import { AppLink } from './HomeBanner';

import balconyImg from './images/about-hero-balcony.jpg';
import cobraImg from './images/about-mission-cobra.jpg';
import archImg from './images/about-story-arch.jpg';
import sunsetImg from './images/sunset-mountain.jpg';
import whyImg from './images/why-foursouls.jpg';
import simpleHeroImg from './images/about-simple-hero.jpg';
import trainerAaradhya from './images/trainer-aaradhya.jpg';
import trainerRohan from './images/trainer-rohan.jpg';
import trainerMeera from './images/trainer-meera.jpg';

export function resolveBlogImageUrl(raw, fallback) {
  if (!raw) return fallback;
  if (typeof raw === 'string') {
    if (raw.startsWith('http://') || raw.startsWith('https://') || raw.startsWith('data:')) {
      return raw;
    }
    if (raw.startsWith('/storage/')) {
      return raw;
    }
    if (raw.startsWith('storage/')) {
      return `/${raw}`;
    }
    return `/storage/${raw.replace(/^\/+/, '')}`;
  }
  return fallback;
}

export const CURATED_ARTICLES = [
  {
    id: 'art-1',
    slug: 'the-art-of-consistency-15-minutes',
    title: 'The Art of Consistency: Why 15 Mindful Minutes Beat an Occasional Hour',
    excerpt: 'True transformation in yoga does not demand hours of rigorous strain. Discover how small, steady daily rituals rewire the nervous system and anchor your peace.',
    category: 'Daily Rituals',
    readTime: '5 min read',
    date: 'Oct 18, 2026',
    videoUrl: 'https://www.youtube.com/embed/inpok4MKVLM',
    videoTitle: '15-Minute Grounding Morning Vinyasa Masterclass',
    author: {
      name: 'Aaradhya Varma',
      role: 'Lead Instructor & Sound Healer',
      avatar: trainerAaradhya,
    },
    image: simpleHeroImg,
    featured: true,
    content: `
      <p class="journal-lead">Many practitioners begin their journey with the belief that progress requires ninety-minute sweat sessions every day. In reality, the nervous system responds far more deeply to calm, uninterrupted regularity than to sporadic bursts of intensity.</p>
      
      <h2>1. The Science of Daily Micro-Practices</h2>
      <p>When you step onto your mat for just fifteen minutes every morning, you send a clear signal of safety to your body. Breath by breath, the sympathetic fight-or-flight response softens, lowering baseline cortisol levels and promoting steady neuroplastic adaptation.</p>
      
      <blockquote>
        "Consistency is not about perfection. It is simply the quiet act of returning to yourself, day after day, without judgment."
      </blockquote>

      <h2>2. Crafting Your 15-Minute Foundation</h2>
      <p>You don't need complicated choreography. A balanced morning flow can be delightfully simple:</p>
      <ul>
        <li><strong>Minutes 0–3:</strong> Mindful grounding in Sukhasana with conscious belly breathing.</li>
        <li><strong>Minutes 3–8:</strong> Gentle spinal awakening through Cat-Cow and gentle side stretches.</li>
        <li><strong>Minutes 8–12:</strong> Low lunges and Downward Facing Dog to awaken circulation.</li>
        <li><strong>Minutes 12–15:</strong> Stillness, gratitude, and a quiet breath before stepping into your day.</li>
      </ul>

      <h2>3. Moving Beyond the All-or-Nothing Mindset</h2>
      <p>On days when energy is low, do not abandon the mat. Five minutes of legs-up-the-wall or gentle neck rolls still honor the habit. The ritual is the anchor; the depth will follow naturally.</p>
    `,
  },
  {
    id: 'art-2',
    slug: '5-simple-yoga-practices-peaceful-morning',
    title: '5 Simple Yoga Practices for a Peaceful Morning',
    excerpt: 'Awaken your body with gentle mobility, deep diaphragmatic breath, and positive presence before the demands of the day begin.',
    category: 'Yoga Practice',
    readTime: '4 min read',
    date: 'Oct 14, 2026',
    author: {
      name: 'Rohan Mehra',
      role: 'Mobility & Hatha Specialist',
      avatar: trainerRohan,
    },
    image: cobraImg,
    content: `
      <p class="journal-lead">Morning is the sacred transition between subconscious rest and conscious action. How we greet our bodies in the first thirty minutes shapes our emotional resilience for the entire day.</p>
      
      <h2>The Five Morning Essentials</h2>
      <p>Before checking your phone or jumping into tasks, gift yourself these five restorative postures:</p>
      <ul>
        <li><strong>1. Balasana (Child's Pose):</strong> Surrender the hips back to heels and allow the forehead to touch the floor. Breathe into the back ribs.</li>
        <li><strong>2. Marjaryasana-Bitilasana (Cat-Cow):</strong> Mobilize each vertebra with fluid coordination between inhale and exhale.</li>
        <li><strong>3. Bhujangasana (Gentle Cobra):</strong> Open the chest, engage the shoulder blades down, and breathe space into the heart center.</li>
        <li><strong>4. Anjaneyasana (Low Lunge):</strong> Release tight hip flexors from long hours of sitting.</li>
        <li><strong>5. Viparita Karani:</strong> Rest the legs up and pause in silent observation of your breath.</li>
      </ul>
    `,
  },
  {
    id: 'art-3',
    slug: 'the-healing-power-of-sound-frequencies',
    title: 'The Healing Power of Sound: Frequency, Breath & Nervous System Harmony',
    excerpt: 'How Tibetan singing bowls and gong vibrations synchronize brainwaves, dissolving chronic tension and inviting deep theta meditation.',
    category: 'Sound & Rest',
    readTime: '6 min read',
    date: 'Oct 11, 2026',
    videoUrl: 'https://www.youtube.com/embed/v7AYKMP6rOE',
    videoTitle: 'Tibetan Sound Healing & Theta Brainwave Restoration',
    author: {
      name: 'Aaradhya Varma',
      role: 'Certified Sound Healer',
      avatar: trainerAaradhya,
    },
    image: balconyImg,
    content: `
      <p class="journal-lead">Sound is not merely something we hear with our ears; it is a tactile vibration felt by every cellular membrane in the physical body.</p>
      <p>Through the phenomenon of acoustic entrainment, the harmonic frequencies produced by high-purity quartz and hammered brass bowls guide racing beta brainwaves down into soothing alpha and meditative theta states.</p>
      <blockquote>
        "In sound healing, the silence between the vibrations is where healing takes root."
      </blockquote>
      <p>Combined with gentle restorative asana, acoustic therapy allows practitioners to experience restorative rest equivalent to hours of uninterrupted deep sleep.</p>
    `,
  },
  {
    id: 'art-4',
    slug: 'pranayama-for-anxiety-relief',
    title: 'Pranayama for Instant Grounding: The 4-7-8 Breathwork Technique',
    excerpt: 'An accessible, science-backed breathing ritual to down-regulate heart rate and bring instantaneous clarity during overwhelming moments.',
    category: 'Breathwork',
    readTime: '3 min read',
    date: 'Oct 08, 2026',
    author: {
      name: 'Meera Iyer',
      role: 'Pranayama & Meditation Guide',
      avatar: trainerMeera,
    },
    image: archImg,
    content: `
      <p class="journal-lead">When anxiety strikes, cognitive reasoning is often bypassed by the amygdala. The breath is your singular conscious remote control to reset the autonomic nervous system.</p>
      <h2>The 4-7-8 Rhythm</h2>
      <p>Inhale quietly through the nose for a count of 4. Hold the breath gently for a count of 7. Exhale with a soft audible sigh through the mouth for a full count of 8. Repeat this cycle 4 times whenever you feel tension rising.</p>
    `,
  },
  {
    id: 'art-5',
    slug: 'creating-a-sacred-home-practice-space',
    title: 'Creating a Sanctuary: How to Set Up an Inspiring Home Practice Corner',
    excerpt: 'You do not need a dedicated room. Learn how small sensory cues — natural light, linen textures, and clean mats — create an inviting sanctuary.',
    category: 'Mindfulness',
    readTime: '5 min read',
    date: 'Oct 03, 2026',
    author: {
      name: 'Rohan Mehra',
      role: 'Movement Guide',
      avatar: trainerRohan,
    },
    image: whyImg,
    content: `
      <p class="journal-lead">The physical environment we practice in subtly informs our state of mind. When your practice space is cluttered or neglected, showing up feels like an uphill effort.</p>
      <p>Dedicate a single corner of your living room or bedroom. Keep a clean, rolled mat, a supportive cork block, and a small ceramic dish with lavender or sandalwood oil. Stepping onto that mat becomes an immediate psychological invitation to exhale.</p>
    `,
  },
  {
    id: 'art-6',
    slug: 'holistic-wellness-beyond-the-mat',
    title: 'Yoga Beyond the Mat: Nourishing Your Body, Mind and Community',
    excerpt: 'How the philosophical tenets of Ahimsa (non-harming) and Santosha (contentment) transform your relationships, work, and inner dialogue.',
    category: 'Holistic Living',
    readTime: '4 min read',
    date: 'Sep 28, 2026',
    author: {
      name: 'Meera Iyer',
      role: 'Pranayama & Meditation Guide',
      avatar: trainerMeera,
    },
    image: sunsetImg,
    content: `
      <p class="journal-lead">Asana is only one of the eight limbs of classical yoga. The true practice begins when you roll up your mat and step out into the complexities of everyday life.</p>
      <p>Practicing non-harming (Ahimsa) in how you speak to yourself, cultivating contentment (Santosha) with your present circumstances, and honoring your body’s need for rest are the deepest expressions of yoga you can ever embody.</p>
    `,
  },
];

const CATEGORIES = [
  'All Articles',
  'Daily Rituals',
  'Yoga Practice',
  'Breathwork',
  'Mindfulness',
  'Sound & Rest',
  'Holistic Living',
];

let cachedBlogArticles = null;

export default function JournalPage({ activeSlug, onSelectArticle }) {
  const [articles, setArticles] = useState(() => cachedBlogArticles || []);
  const [apiDetailArticle, setApiDetailArticle] = useState(null);
  const [isLoading, setIsLoading] = useState(!cachedBlogArticles);
  const [selectedCategory, setSelectedCategory] = useState('All Articles');
  const [searchQuery, setSearchQuery] = useState('');
  const [newsletterEmail, setNewsletterEmail] = useState('');
  const [subscribed, setSubscribed] = useState(false);

  // Fetch all articles from backend
  useEffect(() => {
    let isMounted = true;
    if (!cachedBlogArticles) {
      setIsLoading(true);
    }
    fetch('/api/blog')
      .then((res) => (res.ok ? res.json() : []))
      .then((data) => {
        if (!isMounted || !Array.isArray(data)) {
          setIsLoading(false);
          return;
        }

        const fallbackImages = [simpleHeroImg, cobraImg, archImg, sunsetImg, whyImg, balconyImg];
        const fallbackAvatars = [trainerAaradhya, trainerRohan, trainerMeera];

        const backendArticles = data.map((post, idx) => ({
          id: String(post.id),
          slug: post.slug,
          title: post.title,
          excerpt: post.excerpt || '',
          category: post.category || 'Yoga & Living',
          readTime: post.readTime || `${post.reading_time_minutes || 5} min read`,
          date: post.date || 'Recent',
          videoUrl: post.video_url || null,
          videoTitle: post.video_title || null,
          author: {
            name: post.author?.name || 'FourSouls Guide',
            role: post.author?.role || 'Yoga & Wellness Instructor',
            avatar: post.author?.avatar || fallbackAvatars[idx % fallbackAvatars.length],
          },
          image: resolveBlogImageUrl(post.image || post.featured_image_path, fallbackImages[idx % fallbackImages.length]),
          content: post.content || `<p>${post.excerpt || ''}</p>`,
        }));

        cachedBlogArticles = backendArticles;
        setArticles(backendArticles);
        setIsLoading(false);
      })
      .catch((err) => {
        console.error('Failed to load blog posts:', err);
        if (isMounted) setIsLoading(false);
      });

    return () => {
      isMounted = false;
    };
  }, []);

  // If viewing a specific slug, also fetch single post detail if not found or to ensure latest full content
  useEffect(() => {
    if (!activeSlug) {
      setApiDetailArticle(null);
      return;
    }

    let isMounted = true;
    fetch(`/api/blog/${encodeURIComponent(activeSlug)}`)
      .then((res) => (res.ok ? res.json() : null))
      .then((post) => {
        if (!isMounted || !post || post.message) return;
        const fallbackImages = [simpleHeroImg, cobraImg, archImg, sunsetImg, whyImg, balconyImg];
        const fallbackAvatars = [trainerAaradhya, trainerRohan, trainerMeera];

        setApiDetailArticle({
          id: String(post.id),
          slug: post.slug,
          title: post.title,
          excerpt: post.excerpt,
          category: post.category || 'Yoga & Living',
          readTime: post.readTime || `${post.reading_time_minutes || 5} min read`,
          date: post.date || 'Recent',
          videoUrl: post.video_url || null,
          videoTitle: post.video_title || null,
          author: {
            name: post.author?.name || 'FourSouls Guide',
            role: post.author?.role || 'Yoga & Wellness Instructor',
            avatar: post.author?.avatar || fallbackAvatars[0],
          },
          image: resolveBlogImageUrl(post.image || post.featured_image_path, fallbackImages[0]),
          featured: false,
          content: post.content || `<p>${post.excerpt || ''}</p>`,
        });
      })
      .catch(() => {});

    return () => {
      isMounted = false;
    };
  }, [activeSlug]);

  const currentArticle = useMemo(() => {
    if (!activeSlug) return null;
    if (apiDetailArticle && apiDetailArticle.slug === activeSlug) return apiDetailArticle;
    return articles.find((a) => a.slug === activeSlug) || null;
  }, [activeSlug, apiDetailArticle, articles]);

  const availableCategories = useMemo(() => {
    const set = new Set(['All Articles']);
    articles.forEach((a) => {
      if (a.category && a.category.trim()) {
        set.add(a.category.trim());
      }
    });
    return Array.from(set);
  }, [articles]);

  const filteredArticles = useMemo(() => {
    return articles.filter((article) => {
      const matchCat =
        selectedCategory === 'All Articles' || article.category === selectedCategory;
      const matchSearch =
        searchQuery.trim() === '' ||
        article.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        article.excerpt.toLowerCase().includes(searchQuery.toLowerCase()) ||
        article.category.toLowerCase().includes(searchQuery.toLowerCase());
      return matchCat && matchSearch;
    });
  }, [articles, selectedCategory, searchQuery]);

  const [customVideoUrl, setCustomVideoUrl] = useState('');
  const [showVideoAdder, setShowVideoAdder] = useState(false);
  const [videoInputText, setVideoInputText] = useState('');

  const parseYouTubeEmbed = (url) => {
    if (!url) return '';
    if (url.includes('youtube.com/embed/')) return url;
    const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    return match ? `https://www.youtube.com/embed/${match[1]}` : url;
  };

  const handleSubscribe = (e) => {
    e.preventDefault();
    if (newsletterEmail) {
      setSubscribed(true);
      setNewsletterEmail('');
    }
  };

  // Dedicated Detail Reader View
  if (activeSlug) {
    if (!currentArticle) {
      return (
        <main className="journal-article-detail-page">
          <div className="section-shell article-reader-shell text-center" style={{ padding: '80px 20px', minHeight: '50vh' }}>
            <h2 style={{ fontSize: '28px', color: '#0f2922', marginBottom: '12px' }}>
              {isLoading ? 'Loading Note...' : 'Article Not Found'}
            </h2>
            <p style={{ color: '#666', maxWidth: '440px', margin: '0 auto 24px' }}>
              {isLoading
                ? 'Retrieving latest thoughts and practice reflections from our teachers...'
                : 'The article you are looking for may have been moved or unpublished.'}
            </p>
            <AppLink href="/blog" className="mockup-btn-navy">
              <span>← Back to All Articles</span>
            </AppLink>
          </div>
        </main>
      );
    }

    const related = articles.filter((a) => a.id !== currentArticle.id).slice(0, 3);
    const activeVideo = customVideoUrl || currentArticle.videoUrl;

    return (
      <main className="journal-article-detail-page">
        <div className="section-shell article-reader-shell">
          {/* Back Navigation Bar */}
          <div className="article-back-nav">
            <AppLink href="/blog" className="back-link-btn">
              <span>← Back to All Articles</span>
            </AppLink>
            <div className="article-tags-cluster">
              {activeVideo && <span className="video-badge-pill">▶ Video Guide</span>}
              <span className="article-category-badge">{currentArticle.category}</span>
            </div>
          </div>

          {/* Article Header */}
          <header className="article-reader-header">
            <div className="article-meta-row">
              <span className="article-read-time">✦ {currentArticle.readTime}</span>
              <span className="meta-sep">·</span>
              <span className="article-publish-date">{currentArticle.date}</span>
            </div>
            <h1 className="article-reader-title">{currentArticle.title}</h1>
            <p className="article-reader-excerpt">{currentArticle.excerpt}</p>

            {/* Author Byline */}
            <div className="article-author-card">
              <img
                src={currentArticle.author.avatar}
                alt={currentArticle.author.name}
                className="author-avatar-img"
              />
              <div className="author-details">
                <strong>{currentArticle.author.name}</strong>
                <small>{currentArticle.author.role}</small>
              </div>
            </div>
          </header>

          {/* Embedded Video Player (if article has video or user attached one) */}
          {activeVideo && (
            <div className="article-video-embed-card">
              <div className="article-video-header">
                <span className="video-pill-tag">▶ VIDEO PRACTICE GUIDE</span>
                <h4>{currentArticle.videoTitle || 'Guided Video Masterclass'}</h4>
              </div>
              <div className="article-video-frame">
                <iframe
                  title="Practice Video"
                  src={parseYouTubeEmbed(activeVideo)}
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                />
              </div>
            </div>
          )}

          {/* YouTube Video Link Attacher Bar */}
          <div className="article-attach-video-bar">
            {!showVideoAdder ? (
              <button
                type="button"
                className="btn-attach-youtube"
                onClick={() => setShowVideoAdder(true)}
              >
                <span>🎥</span>
                <span>
                  {activeVideo ? 'Change / Paste Different YouTube Video' : '+ Attach YouTube Practice Video to This Article'}
                </span>
              </button>
            ) : (
              <form
                className="youtube-embed-form"
                onSubmit={(e) => {
                  e.preventDefault();
                  if (videoInputText.trim()) {
                    setCustomVideoUrl(videoInputText.trim());
                    setShowVideoAdder(false);
                  }
                }}
              >
                <input
                  type="url"
                  placeholder="Paste YouTube link (e.g. https://www.youtube.com/watch?v=...)"
                  value={videoInputText}
                  onChange={(e) => setVideoInputText(e.target.value)}
                  required
                />
                <button type="submit" className="mockup-btn-navy">
                  <span>Embed Video</span>
                </button>
                <button
                  type="button"
                  className="btn-cancel-embed"
                  onClick={() => setShowVideoAdder(false)}
                >
                  Cancel
                </button>
              </form>
            )}
          </div>

          {/* Big Featured Image */}
          <div className="article-reader-cover-wrap">
            <img
              src={resolveBlogImageUrl(currentArticle.image, simpleHeroImg)}
              alt={currentArticle.title}
              className="article-reader-cover-img"
              onError={(e) => {
                e.currentTarget.onerror = null;
                e.currentTarget.src = simpleHeroImg;
              }}
            />
          </div>

          {/* Main Content Body */}
          <div
            className="article-reader-body-copy"
            dangerouslySetInnerHTML={{ __html: currentArticle.content }}
          />

          {/* Teacher Reflection Box */}
          <div className="article-reflection-box">
            <div className="reflection-icon">🌿</div>
            <div>
              <h4>Mindful Takeaway</h4>
              <p>
                Take a moment right now: un-clench your jaw, soften your shoulders away from your ears, and take one deep, effortless breath. That is yoga.
              </p>
            </div>
          </div>

          {/* Share & Back */}
          <div className="article-footer-nav">
            <AppLink href="/blog" className="mockup-btn-navy">
              <span>← Explore More Articles</span>
            </AppLink>
            <AppLink href="/plans" className="btn-white-pill">
              <span>Practice With Us Today →</span>
            </AppLink>
          </div>

          {/* Related Articles Row */}
          <div className="article-related-section">
            <h3>You Might Also Enjoy</h3>
            <div className="related-cards-grid">
              {related.map((item) => (
                <AppLink
                  key={item.id}
                  href={`/blog/${item.slug}`}
                  className="related-item-card"
                >
                  <img src={item.image} alt={item.title} className="related-thumb" />
                  <div className="related-info">
                    <span className="related-cat">{item.category}</span>
                    <h4>{item.title}</h4>
                    <small>{item.readTime}</small>
                  </div>
                </AppLink>
              ))}
            </div>
          </div>
        </div>
      </main>
    );
  }

  // Journal Index View (Clean, simple, high-end blog listing)
  return (
    <main className="mockup-journal-page">
      {/* 1. Header Banner */}
      <section className="journal-header-section">
        <div className="section-shell text-center">
          <div className="mockup-pill-badge badge-mint-soft">
            <span>THE FOURSOULS JOURNAL</span>
          </div>
          <h1 className="journal-page-main-title">
            Words on Movement, Stillness & Daily Living
          </h1>
          <p className="journal-page-subtitle">
            Thoughtful articles, practice guides, and mindful insights from our teachers to inspire your journey on and off the mat.
          </p>

          {/* Category Filter Pills & Search */}
          <div className="journal-filter-bar">
            <div className="category-pills-row">
              {availableCategories.map((cat) => (
                <button
                  key={cat}
                  type="button"
                  className={`category-filter-pill ${selectedCategory === cat ? 'active' : ''}`}
                  onClick={() => setSelectedCategory(cat)}
                >
                  {cat}
                </button>
              ))}
            </div>

            <div className="journal-search-wrap">
              <span className="search-icon">🔍</span>
              <input
                type="text"
                placeholder="Search articles by topic..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                aria-label="Search articles"
              />
              {searchQuery && (
                <button
                  type="button"
                  className="clear-search-btn"
                  onClick={() => setSearchQuery('')}
                >
                  ✕
                </button>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Article Grid */}
      <section className="journal-grid-section">
        <div className="section-shell">
          <div className="journal-section-heading-row">
            <h3>
              {selectedCategory === 'All Articles' ? 'Recent Stories & Notes' : selectedCategory}
              <span className="articles-count-badge">({filteredArticles.length})</span>
            </h3>
          </div>

          {isLoading && articles.length === 0 ? (
            <div className="journal-empty-state" style={{ padding: '60px 20px' }}>
              <span className="empty-leaf">🌱</span>
              <h4>Loading Journal Notes...</h4>
              <p>Gathering latest notes and reflections from our teachers...</p>
            </div>
          ) : filteredArticles.length === 0 ? (
            <div className="journal-empty-state">
              <span className="empty-leaf">🍃</span>
              <h4>{searchQuery ? `No articles found for "${searchQuery}"` : 'No articles published yet'}</h4>
              <p>
                {searchQuery
                  ? 'Try searching for a different keyword or browse our categories above.'
                  : 'Check back soon for new reflections and practices.'}
              </p>
              {searchQuery && (
                <button
                  type="button"
                  className="mockup-btn-navy"
                  onClick={() => {
                    setSearchQuery('');
                    setSelectedCategory('All Articles');
                  }}
                >
                  Reset Filters
                </button>
              )}
            </div>
          ) : (
            <div className="journal-articles-grid">
              {filteredArticles.map((article) => (
                <AppLink
                  key={article.id}
                  href={`/blog/${article.slug}`}
                  className="journal-article-card"
                >
                  <div className="article-card-thumb-wrap">
                    <img
                      src={resolveBlogImageUrl(article.image, simpleHeroImg)}
                      alt={article.title}
                      className="article-card-thumb"
                      loading="lazy"
                      onError={(e) => {
                        e.currentTarget.onerror = null;
                        e.currentTarget.src = simpleHeroImg;
                      }}
                    />
                    <div className="card-top-badges">
                      <span className="article-card-category-pill">{article.category}</span>
                      {article.videoUrl && (
                        <span className="article-card-video-pill">▶ Video</span>
                      )}
                    </div>
                  </div>

                  <div className="article-card-body">
                    <div className="article-card-meta">
                      <span>{article.date}</span>
                      <span className="meta-dot">·</span>
                      <span>{article.readTime}</span>
                    </div>

                    <h3 className="article-card-title">{article.title}</h3>
                    <p className="article-card-excerpt">{article.excerpt}</p>

                    <div className="article-card-footer">
                      <div className="article-card-author">
                        <img
                          src={article.author.avatar}
                          alt={article.author.name}
                          className="author-micro-avatar"
                        />
                        <span>{article.author.name}</span>
                      </div>

                      <span className="article-card-cta">
                        <span>Read</span>
                        <span className="card-cta-arrow">→</span>
                      </span>
                    </div>
                  </div>
                </AppLink>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* 4. Mindful Newsletter Block */}
      <section className="journal-newsletter-section">
        <div className="section-shell">
          <div className="journal-newsletter-card">
            <div className="newsletter-card-decor">🌿</div>
            <div className="newsletter-card-content">
              <span className="mockup-pill-badge badge-mint-soft">SUNDAY REFLECTION</span>
              <h2>A Weekly Breath in Your Inbox</h2>
              <p>
                Join 10,000+ mindful practitioners receiving our weekly notes on movement, restorative sequences, and practical mindfulness.
              </p>

              {subscribed ? (
                <div className="newsletter-thankyou-box">
                  <span>✓</span> Thank you! You're now subscribed to the Sunday Reflection note.
                </div>
              ) : (
                <form className="journal-newsletter-form" onSubmit={handleSubscribe}>
                  <input
                    type="email"
                    placeholder="Enter your email address..."
                    value={newsletterEmail}
                    onChange={(e) => setNewsletterEmail(e.target.value)}
                    required
                    aria-label="Email address"
                  />
                  <button type="submit" className="mockup-btn-navy">
                    <span>Subscribe Free</span>
                    <span>→</span>
                  </button>
                </form>
              )}
              <small className="newsletter-micro-note">
                No spam, ever. Unsubscribe with a single click anytime.
              </small>
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}
