/* ============================================================
   Foursouls.in — Application JS (Prompt 2A)
   Progressive enhancement only; forms work without JS.
   ============================================================ */

(function () {
  'use strict';

  /* ---------- Password show/hide toggles ---------- */
  document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.getAttribute('data-password-toggle'));
      if (!input) { return; }
      var isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      var showIcon = btn.querySelector('.icon-show');
      var hideIcon = btn.querySelector('.icon-hide');
      if (showIcon) { showIcon.style.display = isPassword ? 'none' : ''; }
      if (hideIcon) { hideIcon.style.display = isPassword ? '' : 'none'; }
      var label = btn.querySelector('.visually-hidden');
      if (label) {
        label.textContent = isPassword ? 'Hide password' : 'Show password';
      }
      btn.setAttribute('aria-pressed', String(isPassword));
    });
  });

  /* ---------- Account dropdown menu (header) ---------- */
  document.querySelectorAll('[data-dropdown]').forEach(function (wrapper) {
    var trigger = wrapper.querySelector('[data-dropdown-trigger]');
    var panel = wrapper.querySelector('[data-dropdown-panel]');
    if (!trigger || !panel) { return; }

    function setOpen(open) {
      panel.classList.toggle('open', open);
      trigger.setAttribute('aria-expanded', String(open));
    }

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      setOpen(!panel.classList.contains('open'));
    });

    document.addEventListener('click', function (e) {
      if (!wrapper.contains(e.target)) { setOpen(false); }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { setOpen(false); }
    });
  });

  /* ---------- Avatar preview on file select ---------- */
  var avatarInput = document.getElementById('avatar_input');
  var avatarImg = document.getElementById('avatar_preview_img');
  if (avatarInput && avatarImg) {
    avatarInput.addEventListener('change', function () {
      var file = avatarInput.files && avatarInput.files[0];
      if (!file || !file.type || file.type.indexOf('image/') !== 0) { return; }
      var reader = new FileReader();
      reader.onload = function (e) {
        avatarImg.src = e.target.result;
        avatarImg.hidden = false;
        var initials = document.getElementById('avatar_initials');
        if (initials) { initials.style.display = 'none'; }
      };
      reader.readAsDataURL(file);
    });
  }

  /* ---------- Currency selector (header) auto-submit ---------- */
  document.querySelectorAll('[data-auto-submit]').forEach(function (form) {
    form.addEventListener('change', function () {
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        form.submit();
      }
    });
  });
})();