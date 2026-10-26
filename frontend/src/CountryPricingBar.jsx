import React, { useState, useEffect } from 'react';
import {
  COUNTRY_OPTIONS,
  getInitialCurrency,
  fetchVisitorCountry,
} from './countryPricingService';

export default function CountryPricingBar({
  currency,
  onCurrencyChange,
  className = '',
}) {
  const [detectedData, setDetectedData] = useState(() => {
    const initialKey = (currency || getInitialCurrency() || 'inr').toLowerCase();
    return COUNTRY_OPTIONS[initialKey] || COUNTRY_OPTIONS.inr;
  });

  useEffect(() => {
    fetchVisitorCountry((newCurr, meta) => {
      const normalizedCurr = String(newCurr || '').toLowerCase();
      if (normalizedCurr && normalizedCurr !== String(currency || '').toLowerCase()) {
        if (typeof onCurrencyChange === 'function') {
          onCurrencyChange(normalizedCurr);
        }
      }
      if (meta) {
        setDetectedData(meta);
      }
    });

    const handleGeoUpdate = (e) => {
      const { currency: updatedCurr, countryMeta } = e.detail || {};
      const normalizedCurr = String(updatedCurr || '').toLowerCase();
      if (normalizedCurr && normalizedCurr !== String(currency || '').toLowerCase()) {
        if (typeof onCurrencyChange === 'function') {
          onCurrencyChange(normalizedCurr);
        }
      }
      if (countryMeta) {
        setDetectedData(countryMeta);
      }
    };

    window.addEventListener('foursouls_geo_updated', handleGeoUpdate);
    return () => window.removeEventListener('foursouls_geo_updated', handleGeoUpdate);
  }, [currency, onCurrencyChange]);

  const activeKey = String(currency || detectedData?.currency || 'inr').toLowerCase();
  const activeCountry = COUNTRY_OPTIONS[activeKey] || detectedData || COUNTRY_OPTIONS.inr;

  const flag = activeCountry?.flag || '🇮🇳';
  const countryName = activeCountry?.countryName || 'India';
  const symbol = (activeCountry?.currencySymbol || '₹').trim();
  const code = activeCountry?.currencyCode || 'INR';
  const tagline = activeCountry?.tagline || 'Instant UPI, Cards & NetBanking';

  return (
    <div className={`country-pricing-bar-wrap ${className}`}>
      {/* Informative Localized Pricing Pill (Strictly locked to visitor's detected country) */}
      <div className="country-pricing-status-pill">
        <span className="geo-live-dot" title="Country auto-detected" />
        <span className="geo-country-flag">{flag}</span>
        <span className="geo-status-text">
          Showing localized pricing for <strong>{countryName}</strong> ({symbol} {code})
        </span>
      </div>

      <div className="currency-geo-note">
        {tagline}
      </div>
    </div>
  );
}
