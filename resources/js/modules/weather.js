const WEATHER_STATE_KEY = '__legalWeatherWidget';

export default function initWeatherWidget() {
  const weatherIcon = document.getElementById('weather-icon');
  const weatherTemp = document.getElementById('weather-temp');

  if (!weatherIcon || !weatherTemp || window[WEATHER_STATE_KEY]?.ready) return;

  const lat = 31.2001;
  const lon = 29.9187;
  const state = {
    controller: null,
    intervalId: null,
    ready: true
  };

  window[WEATHER_STATE_KEY] = state;

  const updateWeatherIcon = code => {
    let icon = 'tabler-sun';

    if (code >= 1 && code <= 3) icon = 'tabler-cloud';
    else if (code >= 45 && code <= 48) icon = 'tabler-wind';
    else if (code >= 51 && code <= 67) icon = 'tabler-cloud-rain';
    else if (code >= 71 && code <= 77) icon = 'tabler-snowflake';
    else if (code >= 80 && code <= 82) icon = 'tabler-bolt';

    weatherIcon.className = `${icon} icon-base ti icon-md`;
  };

  const fetchWeather = async () => {
    const controller = new AbortController();

    if (state.controller) {
      state.controller.abort();
    }

    state.controller = controller;

    try {
      const response = await fetch(
        `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`,
        { signal: controller.signal }
      );
      const data = await response.json();
      const weather = data.current_weather;

      weatherTemp.innerText = `${Math.round(weather.temperature)}°C`;
      updateWeatherIcon(weather.weathercode);
    } catch (error) {
      if (controller.signal.aborted) return;

      console.error('Weather fetch error:', error);
      weatherTemp.innerText = '!!';
    } finally {
      if (state.controller === controller) {
        state.controller = null;
      }
    }
  };

  const dispose = () => {
    const intervalId = state.intervalId;

    if (intervalId) {
      clearInterval(intervalId);
    }

    if (window.weatherInterval === intervalId) {
      delete window.weatherInterval;
    }

    state.intervalId = null;

    if (state.controller) {
      state.controller.abort();
      state.controller = null;
    }

    delete window[WEATHER_STATE_KEY];
  };

  fetchWeather();

  state.intervalId = setInterval(fetchWeather, 30 * 60 * 1000);
  window.weatherInterval = state.intervalId;
  window.addEventListener('pagehide', dispose, { once: true });
}
