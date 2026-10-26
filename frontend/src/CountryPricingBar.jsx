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
  const [detectedData, setDetectedData] = useState(
    () => COUNTRY_OPTIONS[currency] || COUNTRY_OPTIONS[getInitialCurrency()] || COUNTRY_OPTIONS.inr
  );

  useEffect(() => {
    // Start background geo resolution
    fetchVisitorCountry((newCurr, meta) => {
      if (newCurr && newCurr !== currency) {
        onCurrencyChange(newCurr);
      }
      if (meta) {
        setDetectedData(meta);
      }
    });

    const handleGeoUpdate = (e) => {
      const { currency: updatedCurr, countryMeta } = e.detail || {};
      if (updatedCurr && updatedCurr !== currency) {
        onCurrencyChange(updatedCurr);
      }
      if (countryMeta) {
        setDetectedData(countryMeta);
      }
    };

    window.addEventListener('foursouls_geo_updated', handleGeoUpdate);
    return () => window.removeEventListener('foursouls_geo_updated', handleGeoUpdate);
  }, [currency, onCurrencyChange]);

  const activeCountry = COUNTRY_OPTIONS[currency] || detectedData || COUNTRY_OPTIONS.inr;

  return (
    <div className={`country-pricing-bar-wrap ${className}`}>
      {/* Informative Localized Pricing Pill (Strictly locked to visitor's detected country) */}
      <div className="country-pricing-status-pill">
        <span className="geo-live-dot" title="Country auto-detected" />
        <span className="geo-country-flag">{activeCountry.flag}</span>
        <span className="geo-status-text">
          Showing localized pricing for <strong>{activeCountry.countryName}</strong> ({activeCountry.currencySymbol.trim()} {activeCountry.currencyCode})
        </span>
      </div>

      <div className="currency-geo-note">
        {activeCountry.tagline}
      </div>
    </div>
  );
}
