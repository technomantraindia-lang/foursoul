/**
 * Country-Wise Pricing Service for FourSouls Online Yoga
 *
 * Business Rules:
 *  - India (IN) -> Strictly INR (₹)
 *  - United States (US) & other worldwide countries -> Strictly USD ($)
 *  - UAE (AE) & Gulf region -> AED
 *
 * Automatically detects the visitor's country without giving any manual currency switch options.
 */

export const COUNTRY_OPTIONS = {
  inr: {
    countryCode: 'IN',
    countryName: 'India',
    flag: '🇮🇳',
    currency: 'inr',
    currencyCode: 'INR',
    currencySymbol: '₹',
    displaySymbol: '₹',
    label: 'India (₹ INR)',
    region: 'India',
    badgeText: '🇮🇳 India Pricing',
    tagline: 'Instant UPI (GPay, PhonePe, Paytm), RuPay, Cards & NetBanking',
  },
  aed: {
    countryCode: 'AE',
    countryName: 'United Arab Emirates',
    flag: '🇦🇪',
    currency: 'aed',
    currencyCode: 'AED',
    currencySymbol: 'AED ',
    displaySymbol: 'AED ',
    label: 'UAE & GCC (AED)',
    region: 'United Arab Emirates',
    badgeText: '🇦🇪 UAE Pricing',
    tagline: 'GCC Cards, Apple Pay & Google Pay',
  },
  usd: {
    countryCode: 'US',
    countryName: 'United States & Worldwide',
    flag: '🌍',
    currency: 'usd',
    currencyCode: 'USD',
    currencySymbol: '$',
    displaySymbol: '$',
    label: 'United States & Worldwide ($ USD)',
    region: 'United States & Worldwide',
    badgeText: 'USD Pricing',
    tagline: 'International Credit/Debit Cards & PayPal',
  },
};

const STORAGE_KEY_DETECTED = 'foursouls_detected_geo';

/**
 * Determine country code from browser timezone and locale
 */
export function getClientSideHeuristic() {
  try {
    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    if (/Calcutta|Kolkata|India/i.test(tz)) {
      return { countryCode: 'IN', currency: 'inr', source: 'timezone' };
    }
    if (/Dubai|Muscat|Abu_Dhabi/i.test(tz)) {
      return { countryCode: 'AE', currency: 'aed', source: 'timezone' };
    }
    if (/Riyadh|Qatar|Bahrain|Kuwait/i.test(tz)) {
      return { countryCode: 'AE', currency: 'aed', source: 'timezone' };
    }
    if (/New_York|Chicago|Los_Angeles|Denver|Phoenix|Anchorage|Honolulu|America/i.test(tz)) {
      return { countryCode: 'US', currency: 'usd', source: 'timezone' };
    }
  } catch {
    // Ignore timezone error
  }

  try {
    const langs = navigator.languages || [navigator.language || ''];
    for (const l of langs) {
      if (/-IN$/i.test(l)) return { countryCode: 'IN', currency: 'inr', source: 'locale' };
      if (/-AE$/i.test(l)) return { countryCode: 'AE', currency: 'aed', source: 'locale' };
      if (/-US$/i.test(l)) return { countryCode: 'US', currency: 'usd', source: 'locale' };
    }
  } catch {
    // Ignore locale error
  }

  return { countryCode: 'US', currency: 'usd', source: 'default' };
}

/**
 * Map country code to currency
 */
export function mapCountryToCurrency(countryCode) {
  if (!countryCode) return 'usd';
  const c = String(countryCode).toUpperCase().trim();
  if (c === 'IN') return 'inr';
  if (['AE', 'ARE', 'SA', 'QA', 'KW', 'BH', 'OM'].includes(c)) return 'aed';
  return 'usd';
}

/**
 * Get initial synchronous currency resolution (zero flicker on initial load)
 */
export function getInitialCurrency() {
  // Check cached geo detection (< 24 hrs)
  try {
    const cachedRaw = localStorage.getItem(STORAGE_KEY_DETECTED);
    if (cachedRaw) {
      const cached = JSON.parse(cachedRaw);
      // Valid for 24 hours
      if (cached?.timestamp && Date.now() - cached.timestamp < 24 * 60 * 60 * 1000) {
        if (cached?.currency && COUNTRY_OPTIONS[cached.currency]) {
          return cached.currency;
        }
      }
    }
  } catch {
    // Ignore
  }

  // Client timezone / locale heuristic
  return getClientSideHeuristic().currency;
}

/**
 * Perform asynchronous background geo detection
 */
let geoFetchPromise = null;

export function fetchVisitorCountry(onResolved) {
  if (geoFetchPromise) {
    if (typeof onResolved === 'function') {
      geoFetchPromise.then((curr) => onResolved(curr, COUNTRY_OPTIONS[curr]));
    }
    return geoFetchPromise;
  }

  geoFetchPromise = (async () => {
    let resolvedCountry = null;
    let resolvedCurrency = null;

    // A. Query backend /api/geo/country
    try {
      const res = await fetch('/api/geo/country', {
        headers: { Accept: 'application/json' },
      });
      if (res.ok) {
        const data = await res.json();
        if (data?.country_code && data.country_code !== 'XX') {
          resolvedCountry = data.country_code;
          resolvedCurrency = data.currency || mapCountryToCurrency(resolvedCountry);
        }
      }
    } catch {
      // Backend request error
    }

    // B. Query lightweight public IP geo detection fallback if backend country was null (e.g. localhost)
    if (!resolvedCountry || resolvedCountry === 'XX') {
      try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 2500);

        const geoRes = await fetch('https://api.country.is/', {
          signal: controller.signal,
        });
        clearTimeout(timeoutId);

        if (geoRes.ok) {
          const geoData = await geoRes.json();
          if (geoData?.country) {
            resolvedCountry = geoData.country;
            resolvedCurrency = mapCountryToCurrency(resolvedCountry);
          }
        }
      } catch {
        // Fallback to client-side heuristic
      }
    }

    // C. Fallback to client heuristic if still null
    if (!resolvedCurrency) {
      const heuristic = getClientSideHeuristic();
      resolvedCountry = heuristic.countryCode;
      resolvedCurrency = heuristic.currency;
    }

    // Save detected geo in localStorage
    try {
      localStorage.setItem(
        STORAGE_KEY_DETECTED,
        JSON.stringify({
          countryCode: resolvedCountry,
          currency: resolvedCurrency,
          timestamp: Date.now(),
        })
      );
      // Clean up any legacy manual preference key
      localStorage.removeItem('foursouls_preferred_currency');
    } catch {
      // Ignore
    }

    // Dispatch global event for components to re-render
    try {
      window.dispatchEvent(
        new CustomEvent('foursouls_geo_updated', {
          detail: {
            currency: resolvedCurrency,
            countryMeta: COUNTRY_OPTIONS[resolvedCurrency] || COUNTRY_OPTIONS.usd,
          },
        })
      );
    } catch {
      // Ignore
    }

    if (typeof onResolved === 'function') {
      onResolved(resolvedCurrency, COUNTRY_OPTIONS[resolvedCurrency]);
    }

    return resolvedCurrency;
  })();

  return geoFetchPromise;
}
