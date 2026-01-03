document.addEventListener('DOMContentLoaded', () => {
    // Wishlist remove
    document.querySelectorAll('.wishlist-remove-btn').forEach((btn) => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const card = btn.closest('.col');
            const productId = Number(btn.dataset.productId || 0);
            try {
                const res = await fetch('includes/wishlist-toggle.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId })
                });
                if (res.status === 401) {
                    window.location.href = 'login.php?redirect=profile';
                    return;
                }
                const data = await res.json();
                if (data?.ok && card) {
                    card.remove();
                    if (!document.querySelector('.wishlist-product-card')) {
                        window.location.reload();
                    }
                }
            } catch (err) {
                console.error('Failed to update wishlist', err);
            }
        });
    });

    const initialAddresses = Array.isArray(window.profilePageData?.addresses) ? window.profilePageData.addresses : [];
    const addressCardsEl = document.getElementById('addressCards');
    const addressEmptyEl = document.getElementById('addressEmpty');
    const addressModalEl = document.getElementById('addressModal');
    const addressForm = document.getElementById('addressForm');
    const addressModalLabel = document.getElementById('addressModalLabel');
    const addressSubmitBtn = document.getElementById('addressSubmitBtn');
    const addressError = document.getElementById('addressError');
    const addAddressBtn = document.getElementById('addAddressBtn');
    const addressIdInput = document.getElementById('address_id');
    const countryInput = addressForm?.querySelector('input[name="country"]');
    const deleteAccountBtn = document.getElementById('deleteAccountConfirmBtn');

    const regionSelectEl = document.getElementById('profile_region_select');
    const provinceSelectEl = document.getElementById('profile_province_select');
    const citySelectEl = document.getElementById('profile_city_select');
    const barangaySelectEl = document.getElementById('profile_barangay_select');

    const modal = addressModalEl ? new bootstrap.Modal(addressModalEl) : null;
    let addresses = Array.isArray(initialAddresses) ? initialAddresses.slice() : [];

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (ch) => {
        switch (ch) {
            case '&': return '&amp;';
            case '<': return '&lt;';
            case '>': return '&gt;';
            case '"': return '&quot;';
            case "'": return '&#39;';
            default: return ch;
        }
    });

    const formatAddress = (addr) => [addr.address_line, addr.barangay, addr.city, addr.province, addr.region, addr.zip_code, addr.country]
        .filter(Boolean)
        .join(', ');

    const setError = (msg) => {
        if (!addressError) return;
        if (!msg) {
            addressError.classList.add('d-none');
            addressError.textContent = '';
            return;
        }
        addressError.textContent = msg;
        addressError.classList.remove('d-none');
    };

    const renderAddresses = (list) => {
        if (!addressCardsEl) return;
        addressCardsEl.innerHTML = '';
        if (!list.length) {
            addressEmptyEl?.classList.remove('d-none');
            return;
        }
        addressEmptyEl?.classList.add('d-none');
        list.forEach((addr) => {
            const col = document.createElement('div');
            col.className = 'col-md-6';
            const addressText = escapeHtml(formatAddress(addr));
            col.innerHTML = `
                <div class="border rounded h-100 p-3 position-relative">
                    ${addr.is_default ? '<span class="badge bg-dark position-absolute top-0 end-0 m-3">Default</span>' : ''}
                    <div class="fw-bold text-uppercase text-dark mb-1">${escapeHtml(addr.label || 'Address')}</div>
                    <div class="small text-muted mb-2">${escapeHtml(addr.full_name)}</div>
                    <div class="small text-brand-black">${addressText}</div>
                    <div class="small text-muted mt-2">Phone: ${escapeHtml(addr.phone)}</div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-address" data-id="${addr.id}">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-address" data-id="${addr.id}">Delete</button>
                        ${addr.is_default ? '' : `<button type="button" class="btn btn-sm btn-outline-dark" data-action="make-default" data-id="${addr.id}">Make Default</button>`}
                    </div>
                </div>
            `;
            addressCardsEl.appendChild(col);
        });
    };

    const findAddress = (id) => addresses.find((a) => Number(a.id) === Number(id));

    // Tom Select setup
    let regionsData = [];
    let provincesData = [];
    let citiesData = [];
    let barangaysData = [];

    const fetchJson = async (url) => {
        const res = await fetch(url);
        if (!res.ok) throw new Error('Failed to load ' + url);
        return res.json();
    };

    const tomDefaults = {
        valueField: 'code',
        labelField: 'name',
        searchField: 'name',
        maxItems: 1,
        create: false,
        persist: false,
        allowEmptyOption: true,
        placeholder: 'Select...'
    };

    const regionSelect = regionSelectEl ? new TomSelect(regionSelectEl, { ...tomDefaults, placeholder: 'Select Region...' }) : null;
    const provinceSelect = provinceSelectEl ? new TomSelect(provinceSelectEl, { ...tomDefaults, placeholder: 'Select Province...' }) : null;
    const citySelect = citySelectEl ? new TomSelect(citySelectEl, { ...tomDefaults, placeholder: 'Select City...' }) : null;
    const barangaySelect = barangaySelectEl ? new TomSelect(barangaySelectEl, { ...tomDefaults, placeholder: 'Select Barangay...' }) : null;

    const resetSelect = (ts, disable = true) => {
        if (!ts) return;
        ts.clear(true);
        ts.clearOptions();
        if (disable) ts.disable();
        else ts.enable();
    };

    const loadRegions = async () => {
        if (!regionSelect) return;
        if (!regionsData.length) {
            const regions = await fetchJson('https://raw.githubusercontent.com/isaacdarcilla/philippine-addresses/main/region.json');
            regionsData = regions;
        }
        if (Object.keys(regionSelect.options || {}).length === 0) {
            regionSelect.addOptions(regionsData.map((r) => ({ code: r.region_code, name: r.region_name })));
        }
    };

    const onRegionChange = async (regionCode) => {
        resetSelect(provinceSelect);
        resetSelect(citySelect);
        resetSelect(barangaySelect);
        if (!regionCode) return;
        if (!provincesData.length) {
            provincesData = await fetchJson('https://raw.githubusercontent.com/isaacdarcilla/philippine-addresses/main/province.json');
        }
        const options = provincesData.filter((p) => p.region_code === regionCode).map((p) => ({ code: p.province_code, name: p.province_name }));
        provinceSelect?.addOptions(options);
        provinceSelect?.enable();
    };

    const onProvinceChange = async (provinceCode) => {
        resetSelect(citySelect);
        resetSelect(barangaySelect);
        if (!provinceCode) return;
        if (!citiesData.length) {
            citiesData = await fetchJson('https://raw.githubusercontent.com/isaacdarcilla/philippine-addresses/main/city.json');
        }
        const options = citiesData.filter((c) => c.province_code === provinceCode).map((c) => ({ code: c.city_code, name: c.city_name }));
        citySelect?.addOptions(options);
        citySelect?.enable();
    };

    const onCityChange = async (cityCode) => {
        resetSelect(barangaySelect);
        if (!cityCode) return;
        if (!barangaysData.length) {
            barangaysData = await fetchJson('https://raw.githubusercontent.com/isaacdarcilla/philippine-addresses/main/barangay.json');
        }
        const options = barangaysData.filter((b) => b.city_code === cityCode).map((b) => ({ code: b.brgy_code, name: b.brgy_name }));
        barangaySelect?.addOptions(options);
        barangaySelect?.enable();
    };

    const findByName = (collection, nameKey, codeKey, value) => {
        const target = (value || '').toLowerCase().trim();
        if (!target) return '';
        const match = collection.find((item) => (item[nameKey] || '').toLowerCase() === target);
        return match ? match[codeKey] : '';
    };

    const getSelectName = (ts) => {
        if (!ts) return '';
        const val = ts.getValue();
        if (!val) return '';
        const opt = ts.options?.[val];
        return opt?.name || opt?.text || '';
    };

    const resetAddressSelects = () => {
        resetSelect(regionSelect, false);
        resetSelect(provinceSelect);
        resetSelect(citySelect);
        resetSelect(barangaySelect);
    };

    const showToast = (message) => {
        const container = document.createElement('div');
        container.className = 'position-fixed top-0 start-50 translate-middle-x p-3';
        container.style.zIndex = 2000;
        const toast = document.createElement('div');
        toast.className = 'alert alert-success shadow-sm mb-0';
        toast.textContent = message;
        container.appendChild(toast);
        document.body.appendChild(container);
        setTimeout(() => container.remove(), 2200);
    };

    const applyAddressToForm = async (addr) => {
        if (!addr || !addressForm) return;
        addressIdInput.value = addr.id || '';
        addressForm.querySelector('input[name="label"]').value = addr.label || '';
        addressForm.querySelector('input[name="full_name"]').value = addr.full_name || '';
        addressForm.querySelector('input[name="phone"]').value = addr.phone || '';
        addressForm.querySelector('input[name="address_line"]').value = addr.address_line || '';
        addressForm.querySelector('input[name="zip_code"]').value = addr.zip_code || '';
        addressForm.querySelector('input[name="country"]').value = addr.country || 'Philippines';
        const def = addressForm.querySelector('input[name="is_default"]');
        if (def) {
            def.checked = Number(addr.is_default) === 1;
        }
        setError('');
        if (addressModalLabel) addressModalLabel.textContent = 'Edit Address';
        if (addressSubmitBtn) addressSubmitBtn.textContent = 'Update Address';

        try {
            await loadRegions();
            const regionCode = findByName(regionsData, 'region_name', 'region_code', addr.region);
            if (regionCode && regionSelect) {
                regionSelect.setValue(regionCode, true);
                await onRegionChange(regionCode);
            }

            const provinceCode = findByName(provincesData, 'province_name', 'province_code', addr.province);
            if (provinceCode && provinceSelect) {
                provinceSelect.setValue(provinceCode, true);
                await onProvinceChange(provinceCode);
            }

            const cityCode = findByName(citiesData, 'city_name', 'city_code', addr.city);
            if (cityCode && citySelect) {
                citySelect.setValue(cityCode, true);
                await onCityChange(cityCode);
            }

            const barangayCode = findByName(barangaysData, 'brgy_name', 'brgy_code', addr.barangay);
            if (barangayCode && barangaySelect) {
                barangaySelect.setValue(barangayCode, true);
            }
        } catch (err) {
            console.error('Failed to populate address', err);
        }
    };

    const resetForm = () => {
        if (!addressForm) return;
        addressForm.reset();
        addressIdInput.value = '';
        if (countryInput && !countryInput.value) countryInput.value = 'Philippines';
        if (addressModalLabel) addressModalLabel.textContent = 'Add Address';
        if (addressSubmitBtn) addressSubmitBtn.textContent = 'Save Address';
        setError('');
        resetAddressSelects();
        loadRegions().catch((err) => console.error(err));
    };

    const refreshAddresses = async () => {
        try {
            const res = await fetch('includes/address-list.php');
            if (res.status === 401) {
                window.location.href = 'login.php?redirect=profile';
                return;
            }
            const data = await res.json();
            if (data?.ok && Array.isArray(data.data)) {
                addresses = data.data;
                renderAddresses(addresses);
            }
        } catch (err) {
            console.error('Failed to load addresses', err);
        }
    };

    const submitAddress = async (payload) => {
        const isEdit = !!payload.id;
        const url = isEdit ? 'includes/address-update.php' : 'includes/address-create.php';
        try {
            addressSubmitBtn?.setAttribute('disabled', 'disabled');
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data?.ok) {
                setError(data?.error || 'Unable to save address.');
                return false;
            }
            await refreshAddresses();
            modal?.hide();
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            showToast('Address saved successfully');
            return true;
        } catch (err) {
            console.error('Failed to save address', err);
            setError('Something went wrong. Please try again.');
            return false;
        } finally {
            addressSubmitBtn?.removeAttribute('disabled');
        }
    };

    const deleteAddress = async (id) => {
        if (!id) return;
        if (!confirm('Delete this address?')) return;
        try {
            const res = await fetch('includes/address-delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            });
            const data = await res.json();
            if (!res.ok || !data?.ok) {
                alert(data?.error || 'Unable to delete address.');
                return;
            }
            await refreshAddresses();
        } catch (err) {
            console.error('Failed to delete address', err);
            alert('Something went wrong.');
        }
    };

    const makeDefault = async (id) => {
        const addr = findAddress(id);
        if (!addr) return;
        await submitAddress({ ...addr, is_default: 1 });
    };

    if (regionSelect) {
        regionSelect.on('change', onRegionChange);
    }
    if (provinceSelect) {
        provinceSelect.on('change', onProvinceChange);
    }
    if (citySelect) {
        citySelect.on('change', onCityChange);
    }

    loadRegions().catch((err) => console.error(err));

    if (addressForm) {
        addressForm.addEventListener('submit', async (evt) => {
            evt.preventDefault();
            const payload = {
                id: addressIdInput.value ? Number(addressIdInput.value) : undefined,
                label: addressForm.label?.value.trim() || '',
                full_name: addressForm.full_name?.value.trim() || '',
                phone: addressForm.phone?.value.trim() || '',
                address_line: addressForm.address_line?.value.trim() || '',
                region: getSelectName(regionSelect),
                province: getSelectName(provinceSelect),
                city: getSelectName(citySelect),
                barangay: getSelectName(barangaySelect),
                zip_code: addressForm.zip_code?.value.trim() || '',
                country: addressForm.country?.value.trim() || 'Philippines',
                is_default: addressForm.is_default?.checked ? 1 : 0,
            };

            const required = ['full_name', 'phone', 'address_line', 'region', 'province', 'city', 'barangay', 'zip_code'];
            for (const field of required) {
                if (!payload[field]) {
                    setError('Please complete all required fields.');
                    return;
                }
            }

            await submitAddress(payload);
        });
    }

    if (addressCardsEl) {
        addressCardsEl.addEventListener('click', async (evt) => {
            const btn = evt.target.closest('button[data-action]');
            if (!btn) return;
            const action = btn.dataset.action;
            const id = Number(btn.dataset.id || 0);
            if (action === 'edit-address') {
                const addr = findAddress(id);
                if (addr) {
                    resetForm();
                    await applyAddressToForm(addr);
                    modal?.show();
                }
            } else if (action === 'delete-address') {
                deleteAddress(id);
            } else if (action === 'make-default') {
                makeDefault(id);
            }
        });
    }

    if (addAddressBtn) {
        addAddressBtn.addEventListener('click', () => {
            resetForm();
        });
    }

    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', async () => {
            const originalText = deleteAccountBtn.textContent;
            deleteAccountBtn.setAttribute('disabled', 'disabled');
            deleteAccountBtn.textContent = 'Deleting...';
            try {
                const res = await fetch('includes/delete_account.php', { method: 'POST' });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data?.ok) {
                    alert(data?.error || 'Unable to delete account.');
                    return;
                }
                window.location.href = 'logout.php';
            } catch (err) {
                console.error('Failed to delete account', err);
                alert('Something went wrong. Please try again.');
            } finally {
                deleteAccountBtn.removeAttribute('disabled');
                deleteAccountBtn.textContent = originalText;
            }
        });
    }

    resetAddressSelects();
    renderAddresses(addresses);
});
