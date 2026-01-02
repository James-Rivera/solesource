<?php
include __DIR__ . '/products.php';
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Account';
?>
<header>
	<nav class="navbar navbar-expand-lg navbar-dark bg-brand-black py-3 border-bottom border-brand-dark-gray">
		<div class="container-xxl align-items-center">
			<a class="navbar-brand d-flex align-items-center" href="index.php">
				<img src="assets/svg/logo-big-white.svg" alt="SoleSource Logo" height="30">
			</a>
			<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"
				aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="topNav">
				<div class="d-lg-flex align-items-center w-100 gap-3 gap-lg-4">
					<div class="search-container position-relative flex-grow-1 my-3 my-lg-0 order-lg-2">
						<input id="globalSearchInput" class="form-control search-pill text-end" type="search" placeholder="Search" aria-label="Search">
						<div id="globalSearchResults" class="global-search-dropdown d-none"></div>
					</div>

					<ul class="navbar-nav align-items-center gap-lg-3 order-lg-3 flex-row flex-lg-row gap-3 mb-2 mb-lg-0 ms-lg-3 small">
						<li class="nav-item">
							<a class="nav-link px-0" href="#">About</a>
						</li>
						<li class="nav-item">
							<button class="nav-link px-0 btn btn-link text-decoration-none text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" aria-controls="cartDrawer" aria-label="Open cart">
								<i class="bi bi-cart3 fs-5"></i>
							</button>
						</li>
					</ul>

					<div class="d-flex align-items-center gap-2 order-lg-4 flex-wrap ms-lg-3">
						<?php if (!$isLoggedIn): ?>
							<a class="btn btn-login" href="login.php">Log In</a>
							<a class="btn btn-signup" href="signup.php">Signup</a>
						<?php else: ?>
							<div class="dropdown">
								<button class="btn account-btn d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="bi bi-person-circle fs-5"></i>
									<span class="d-none d-md-inline">Hello, <?php echo htmlspecialchars($userName); ?></span>
								</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
										<li><a class="dropdown-item" href="admin/index.php">Admin Panel</a></li>
										<li><hr class="dropdown-divider"></li>
									<?php endif; ?>
									<li><a class="dropdown-item" href="profile.php">Profile</a></li>
									<li><a class="dropdown-item" href="orders.php">Orders</a></li>
									<li><hr class="dropdown-divider"></li>
									<li><a class="dropdown-item" href="logout.php">Log out</a></li>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</nav>

	<nav class="bg-brand-black border-bottom border-brand-dark-gray py-1">
		<div class="container-xxl">
			<div class="d-flex justify-content-center gap-4 gap-md-5 py-3 small category-links flex-wrap">
				<a class="text-inactive text-decoration-none" href="shop.php">All</a>
				<a class="text-inactive text-decoration-none" href="shop.php?brand=Nike">Nike</a>
				<a class="text-inactive text-decoration-none" href="shop.php?brand=Adidas">Adidas</a>
				<a class="text-inactive text-decoration-none" href="shop.php?brand=Puma">Puma</a>
				<a class="text-inactive text-decoration-none" href="shop.php?brand=Onitsuka">Onitsuka Tiger</a>
				<a class="text-inactive text-decoration-none" href="shop.php?brand=Asics">Asics</a>
				<a class="text-inactive text-decoration-none" href="shop.php?brand=Fila">Fila</a>
			</div>
		</div>
	</nav>
</header>
<style>
.global-search-dropdown {
	position: absolute;
	top: 100%;
	right: 0;
	left: 0;
	background: #fff;
	border: 1px solid var(--brand-dark-gray);
	border-radius: 8px;
	padding: 8px 0;
	z-index: 1000;
	box-shadow: 0 10px 24px rgba(0,0,0,0.25);
}
.global-search-item { display: flex; align-items: center; gap: 12px; padding: 8px 12px; color: #111; }
.global-search-item:hover { background: #f5f5f5; text-decoration: none; }
.global-search-thumb { width: 44px; height: 44px; object-fit: contain; }
.global-search-name { font-weight: 700; text-transform: uppercase; color: #000; font-size: 0.9rem; }
.global-search-brand { color: #777; font-size: 0.75rem; text-transform: uppercase; }
.global-search-price { margin-left: auto; color: #000; font-weight: 600; font-size: 0.9rem; }
.account-btn {
	color: #fff;
	background: transparent;
	border: 1px solid transparent;
	padding: 6px 10px;
}
.account-btn:hover {
	color: #f2f2f2;
	border-color: #3a3a3a;
}
</style>

<script>
const input = document.getElementById('globalSearchInput');
const dropdown = document.getElementById('globalSearchResults');

function renderResults(items) {
	if (!items.length) { dropdown.classList.add('d-none'); dropdown.innerHTML = ''; return; }
	dropdown.innerHTML = items.slice(0,5).map(p => `
		<a class="global-search-item" href="product-details.php?id=${encodeURIComponent(p.id)}">
			<img class="global-search-thumb" src="${p.image}" alt="${p.name}">
			<div>
				<div class="global-search-name">${p.name}</div>
				<div class="global-search-brand">${p.brand}</div>
			</div>
			<div class="global-search-price">${p.price}</div>
		</a>
	`).join('');
	dropdown.classList.remove('d-none');
}

let searchController = null;

async function queryProducts(term) {
	if (!term) { dropdown.classList.add('d-none'); dropdown.innerHTML=''; return; }
	if (searchController) { searchController.abort(); }
	searchController = new AbortController();

	try {
		const res = await fetch(`includes/search.php?q=${encodeURIComponent(term)}`, { signal: searchController.signal });
		if (!res.ok) throw new Error('search failed');
		const data = await res.json();
		renderResults(data.results || []);
	} catch (err) {
		console.error(err);
		dropdown.classList.add('d-none');
		dropdown.innerHTML='';
	}
}

input?.addEventListener('input', (e) => queryProducts(e.target.value.trim()));
input?.addEventListener('focus', (e) => queryProducts(e.target.value.trim()));
input?.addEventListener('blur', () => setTimeout(() => { dropdown.classList.add('d-none'); }, 150));
</script>
