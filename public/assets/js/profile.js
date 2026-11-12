/* Profil : choix de la photo avec aperçu immédiat, glisser-déposer et réduction avant envoi. */
(function () {
  'use strict';
  var input = document.getElementById('avatarInput');
  if (!input) return;
  var form = document.getElementById('avatarForm');
  var preview = document.getElementById('avatarPreview');
  var save = document.getElementById('avatarSave');
  var zone = document.getElementById('avatarZone');
  var msg = document.getElementById('avatarMessage');
  var MAX_SIDE = 1024, MAX_BYTES = 4 * 1024 * 1024;

  function say(text, bad) { if (msg) { msg.textContent = text || ''; msg.className = 'small mt-2 ' + (bad ? 'text-danger' : 'text-muted'); } }

  function show(url) {
    preview.innerHTML = '';
    var img = document.createElement('img');
    img.src = url; img.alt = 'Aperçu de la photo';
    img.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block';
    preview.appendChild(img);
  }

  function handle(file) {
    if (!file) return;
    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { say('Format non pris en charge : utilisez JPEG, PNG ou WebP.', true); return; }
    var url = URL.createObjectURL(file);
    var image = new Image();
    image.onload = function () {
      show(url);
      // Réduction dans le navigateur : envoi rapide, même avec une photo de 12 Mpx.
      var ratio = Math.min(1, MAX_SIDE / Math.max(image.width, image.height));
      var canvas = document.createElement('canvas');
      canvas.width = Math.round(image.width * ratio); canvas.height = Math.round(image.height * ratio);
      var ctx = canvas.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
      canvas.toBlob(function (blob) {
        if (!blob || blob.size > MAX_BYTES) { say('Photo trop volumineuse (4 Mo maximum).', true); return; }
        try {
          var transfer = new DataTransfer();
          transfer.items.add(new File([blob], 'photo.jpg', { type: 'image/jpeg' }));
          input.files = transfer.files;
        } catch (e) { /* navigateur ancien : on envoie le fichier d'origine */ }
        save.disabled = false;
        say('Aperçu prêt. Cliquez sur « Enregistrer la photo ».');
      }, 'image/jpeg', 0.9);
    };
    image.onerror = function () { say('Image illisible.', true); };
    image.src = url;
  }

  input.addEventListener('change', function () { handle(input.files && input.files[0]); });
  ['dragenter', 'dragover'].forEach(function (name) {
    zone.addEventListener(name, function (e) { e.preventDefault(); zone.classList.add('border-primary'); });
  });
  ['dragleave', 'drop'].forEach(function (name) {
    zone.addEventListener(name, function (e) { e.preventDefault(); zone.classList.remove('border-primary'); });
  });
  zone.addEventListener('drop', function (e) { if (e.dataTransfer.files.length) handle(e.dataTransfer.files[0]); });
  form.addEventListener('submit', function () { save.disabled = true; save.textContent = 'Envoi…'; });
})();
