(function () {
  'use strict';
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () { navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {}); });
  }
  var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  var KEY = 'pwa-banner-dismissed';
  function dismissed() { try { return localStorage.getItem(KEY) === '1'; } catch (e) { return false; } }
  function remember() { try { localStorage.setItem(KEY, '1'); } catch (e) {} }
  if (standalone || dismissed()) return;

  function banner(text, withButton, onInstall) {
    var bar = document.createElement('div');
    bar.setAttribute('role', 'dialog');
    bar.style.cssText = 'position:fixed;left:12px;right:12px;bottom:calc(12px + env(safe-area-inset-bottom));z-index:2000;max-width:460px;margin:0 auto;background:#173b63;color:#fff;border-radius:14px;padding:12px 14px;display:flex;gap:10px;align-items:center;box-shadow:0 8px 28px rgba(0,0,0,.3);font:14px/1.4 system-ui,sans-serif';
    var span = document.createElement('span'); span.style.flex = '1'; span.textContent = text; bar.appendChild(span);
    if (withButton) {
      var b = document.createElement('button'); b.textContent = 'Installer';
      b.style.cssText = 'background:#fff;color:#173b63;border:0;border-radius:8px;padding:8px 14px;font-weight:600;cursor:pointer';
      b.onclick = function () { bar.remove(); onInstall(); }; bar.appendChild(b);
    }
    var x = document.createElement('button'); x.textContent = '×'; x.setAttribute('aria-label', 'Fermer');
    x.style.cssText = 'background:none;border:0;color:#fff;font-size:22px;line-height:1;cursor:pointer';
    x.onclick = function () { remember(); bar.remove(); }; bar.appendChild(x);
    document.body.appendChild(bar);
  }

  var deferred = null;
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault(); deferred = e;
    banner('Installez Cabinet Comptable sur votre appareil.', true, function () {
      deferred.prompt(); deferred.userChoice.then(function () { deferred = null; remember(); });
    });
  });
  window.addEventListener('appinstalled', remember);

  var ua = navigator.userAgent;
  var ios = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  if (ios && /Safari/.test(ua) && !/CriOS|FxiOS/.test(ua)) {
    window.addEventListener('load', function () {
      setTimeout(function () { banner('Pour installer : touchez Partager puis « Sur l\'écran d\'accueil ».', false); }, 2500);
    });
  }
})();
