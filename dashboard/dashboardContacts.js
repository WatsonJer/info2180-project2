document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('contacts-table');
  const filterButtons = document.querySelectorAll('.filter-btn');
  let currentFilter = 'all';

  function setActiveButton(filter) {
    filterButtons.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.filter === filter);
    });
  }

  async function loadContacts(filter) {
    container.innerHTML = '<div class="loading">Loading contacts...</div>';
    try {
      const resp = await fetch(`../contacts/listContacts.php?filter=${encodeURIComponent(filter)}`, { credentials: 'same-origin' });
      if (!resp.ok) throw new Error('Network error');
      const data = await resp.json();
      if (!data.success) throw new Error('Failed to load');
      renderTable(data.contacts);
    } catch (err) {
      console.error(err);
      container.innerHTML = '<div class="error-message">Could not load contacts. Please try again.</div>';
    }
  }

  function mapTypeLabel(type) {
    if (type === 'salesLead') return 'Sales Lead';
    if (type === 'support') return 'Support';
    return type || '';
  }

  function renderTable(contacts) {
    if (!contacts || contacts.length === 0) {
      container.innerHTML = '<div class="table-responsive"><div class="table">No contacts found.</div></div>';
      return;
    }

    const tbl = document.createElement('table');
    tbl.className = 'table';

    const thead = document.createElement('thead');
    thead.innerHTML = `
      <tr>
        <th>Contact</th>
        <th>Email</th>
        <th>Company</th>
        <th>Type</th>
        <th></th>
      </tr>`;

    const tbody = document.createElement('tbody');

    contacts.forEach(c => {
      const tr = document.createElement('tr');
      const title = c.title ? (c.title.endsWith('.') ? c.title : c.title + '.') : '';
      const fullName = `${title} ${c.firstname} ${c.lastname}`.trim();

      tr.innerHTML = `
        <td class="first-col"><div class="avatar">${escapeInitials(c.firstname, c.lastname)}</div>${escapeHtml(fullName)}</td>
        <td class="email-col">${escapeHtml(c.email)}</td>
        <td>${escapeHtml(c.company)}</td>
        <td>${escapeHtml(mapTypeLabel(c.type))}</td>
        <td class="actions"><a class="btn-small" href="../contacts/viewContact.php?id=${encodeURIComponent(c.id)}">View</a></td>
      `;

      tbody.appendChild(tr);
    });

    tbl.appendChild(thead);
    tbl.appendChild(tbody);

    const wrapper = document.createElement('div');
    wrapper.className = 'table-responsive';
    wrapper.appendChild(tbl);

    container.innerHTML = '';
    container.appendChild(wrapper);
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function (ch) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
    });
  }

  function escapeInitials(first, last) {
    const a = (first || '').trim(), b = (last || '').trim();
    const letters = (a.charAt(0) || '') + (b.charAt(0) || '');
    return escapeHtml(letters.toUpperCase());
  }

  // wire up filter buttons
  filterButtons.forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const f = this.dataset.filter || 'all';
      if (f === currentFilter) return;
      currentFilter = f;
      setActiveButton(f);
      loadContacts(f);
    });
  });

  // initial load
  setActiveButton(currentFilter);
  loadContacts(currentFilter);
});