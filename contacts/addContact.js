document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('contact-form');
  if (!form) return;

  const messages = document.getElementById('form-messages');
  const saveBtn = document.getElementById('save-btn');
  const initialDisabled = saveBtn ? saveBtn.disabled : false;
  const originalBtnText = saveBtn ? saveBtn.textContent : 'Save';

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function (ch) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      })[ch];
    });
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    // Clear previous messages
    messages.innerHTML = '';

    // Use browser validation
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    if (!saveBtn) return;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    const fd = new FormData(form);
    fd.append('ajax', '1');

    try {
      const resp = await fetch(form.action, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });

      if (!resp.ok) throw new Error('Network error');

      const data = await resp.json();

      if (data.success) {
        messages.innerHTML = '<div class="success-message">' + escapeHtml(data.message) + '</div>';
        form.reset();
        // reset select to placeholder if available
        const assigned = document.getElementById('assigned');
        if (assigned && !assigned.disabled) assigned.selectedIndex = 0;
      } else {
        messages.innerHTML = '<div class="error-message">' + escapeHtml(data.message) + '</div>';
      }
    } catch (err) {
      console.error(err);
      messages.innerHTML = '<div class="error-message">An error occurred. Please try again.</div>';
    } finally {
      saveBtn.disabled = initialDisabled;
      saveBtn.textContent = originalBtnText;
    }
  });
});