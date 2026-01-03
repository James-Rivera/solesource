<?php
include __DIR__ . '/products.php';
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Account';
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
	foreach ($_SESSION['cart'] as $line) {
		$cartCount += isset($line['qty']) ? (int)$line['qty'] : 0;
	}
}
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$isCartFlow = in_array($currentPage, ['cart.php', 'checkout.php'], true);
$navBrands = [];
$navSports = [];
$navGenders = [];

$brands_rs = $conn->query("SELECT DISTINCT brand FROM products WHERE status='active' AND brand <> '' ORDER BY brand ASC");
if ($brands_rs) {
	while ($row = $brands_rs->fetch_assoc()) {
		$navBrands[] = $row['brand'];
	}
}

$sports_rs = $conn->query("SELECT DISTINCT sport FROM products WHERE status='active' AND sport IS NOT NULL AND sport <> '' ORDER BY sport ASC");
if ($sports_rs) {
	while ($row = $sports_rs->fetch_assoc()) {
		$navSports[] = $row['sport'];
	}
}

$genders_rs = $conn->query("SELECT DISTINCT gender FROM products WHERE status='active' AND gender <> '' ORDER BY gender ASC");
if ($genders_rs) {
	while ($row = $genders_rs->fetch_assoc()) {
		$navGenders[] = $row['gender'];
	}
}

if (empty($navGenders)) { $navGenders = ['Men','Women','Unisex']; }
if (empty($navBrands)) { $navBrands = ['Nike','Adidas','Asics','Puma']; }
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
							<a class="nav-link px-0 d-inline-flex align-items-center text-white" href="profile.php?tab=profile#wishlist" aria-label="Wishlist">
								<i class="bi bi-heart fs-5"></i>
							</a>
						</li>
						<li class="nav-item position-relative">
							<?php if ($isCartFlow): ?>
								<a class="nav-link px-0 btn btn-link text-decoration-none text-white position-relative cart-trigger" href="cart.php" aria-label="Cart">
									<i class="bi bi-cart3 fs-5"></i>
									<span id="header-cart-count" class="position-absolute badge rounded-pill bg-danger header-cart-badge <?php echo $cartCount === 0 ? 'd-none' : ''; ?>">
										<?php echo $cartCount; ?>
									</span>
								</a>
							<?php else: ?>
								<button class="nav-link px-0 btn btn-link text-decoration-none text-white position-relative cart-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" aria-controls="cartDrawer" aria-label="Open cart">
									<i class="bi bi-cart3 fs-5"></i>
									<span id="header-cart-count" class="position-absolute badge rounded-pill bg-danger header-cart-badge <?php echo $cartCount === 0 ? 'd-none' : ''; ?>">
										<?php echo $cartCount; ?>
									</span>
								</button>
							<?php endif; ?>
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
									<span class="d-none d-md-inline"><?php echo htmlspecialchars($userName); ?></span>
								</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
										<li><a class="dropdown-item" href="admin/index.php">Admin Panel</a></li>
										<li><hr class="dropdown-divider"></li>
									<?php endif; ?>
									<li><a class="dropdown-item" href="profile.php">Profile</a></li>
									<li><a class="dropdown-item" href="profile.php?tab=orders">Orders</a></li>
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

	<nav class="bg-brand-black border-bottom border-brand-dark-gray py-1 mega-nav-wrapper">
		<div class="container-xxl">
			<ul class="mega-nav">
				<?php foreach ($navGenders as $gender): ?>
				<li class="mega-item has-panel">
					<a href="shop.php?gender=<?php echo urlencode($gender); ?>" class="mega-link"><?php echo htmlspecialchars($gender); ?></a>
					<div class="mega-panel">
						<div class="mega-panel-inner">
							<div class="mega-col">
								<div class="mega-title">Spotlight</div>
								<a href="shop.php?gender=<?php echo urlencode($gender); ?>&sort=new">New Releases</a>
								<a href="shop.php?gender=<?php echo urlencode($gender); ?>&sort=best">Best Sellers</a>
								<a href="shop.php?gender=<?php echo urlencode($gender); ?>&is_featured=1">Featured</a>
							</div>
							<?php if (!empty($navBrands)): ?>
							<div class="mega-col">
								<div class="mega-title">Brands</div>
								<?php foreach ($navBrands as $brand): ?>
									<a href="shop.php?gender=<?php echo urlencode($gender); ?>&brand=<?php echo urlencode($brand); ?>"><?php echo htmlspecialchars($brand); ?></a>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
							<?php if (!empty($navSports)): ?>
							<div class="mega-col">
								<div class="mega-title">Sport</div>
								<?php foreach ($navSports as $sport): ?>
									<a href="shop.php?gender=<?php echo urlencode($gender); ?>&sport=<?php echo urlencode($sport); ?>"><?php echo htmlspecialchars($sport); ?></a>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</li>
				<?php endforeach; ?>
				<li class="mega-item has-panel">
					<a href="shop.php" class="mega-link">Brands</a>
					<div class="mega-panel">
						<div class="mega-panel-inner">
							<?php if (!empty($navBrands)): ?>
							<div class="mega-col">
								<div class="mega-title">Top Brands</div>
								<?php foreach ($navBrands as $brand): ?>
									<a href="shop.php?brand=<?php echo urlencode($brand); ?>"><?php echo htmlspecialchars($brand); ?></a>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
							<div class="mega-col">
								<div class="mega-title">Shop by Gender</div>
								<?php foreach ($navGenders as $gender): ?>
									<a href="shop.php?gender=<?php echo urlencode($gender); ?>"><?php echo htmlspecialchars($gender); ?></a>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</li>
			</ul>
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
.cart-trigger { position: relative; display: inline-flex; align-items: center; }
.header-cart-badge {
	position: absolute;
	top: -8px;
	right: -10px;
	z-index: 10;
}
.dropdown-menu {
	z-index: 1200;
}
.mega-nav-wrapper { position: relative; }
.mega-nav { list-style: none; margin: 0; padding: 0; display: flex; gap: 2rem; justify-content: center; align-items: center; }
.mega-link { color: #fff; text-decoration: none; font-weight: 700; letter-spacing: 0.4px; }
.mega-item { position: relative; padding: 0.75rem 0; }
.mega-panel {
	position: fixed;
	left: 0;
	right: 0;
	width: 100vw;
	top: var(--mega-top, 110px);
	background: #fff;
	padding: 2.5rem 0;
	box-shadow: 0 16px 36px rgba(0,0,0,0.2);
	border-radius: 0;
	border: 1px solid #e5e5e5;
	opacity: 0;
	visibility: hidden;
	pointer-events: none;
	transform: translateY(14px);
	transition: opacity 180ms ease, transform 180ms ease, visibility 0ms linear 180ms;
	z-index: 1400;
}
.mega-panel-inner {
	width: min(1200px, 92vw);
	margin: 0 auto;
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
	gap: 1.75rem 2.25rem;
}
.mega-col { min-width: 200px; flex: 1 1 220px; }
.mega-col a {
	display: block;
	padding: 6px 0;
	color: #757575;
	font-weight: 400;
	line-height: 1.3;
	transition: color 0.2s ease-in-out;
}
.mega-col a:hover { color: #000; }
.mega-title { text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.6px; font-weight: 800; color: #000; margin-bottom: 0.6rem; }
.mega-item:hover > .mega-panel,
.mega-item:focus-within > .mega-panel { opacity: 1; visibility: visible; pointer-events: auto; transform: translateY(0); transition-delay: 0ms; }
@media (max-width: 992px) {
	.mega-nav { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
	.mega-item { width: 100%; }
	.mega-panel { position: static; width: 100%; padding: 1.5rem 0; box-shadow: none; border-left: 0; border-right: 0; opacity: 0; visibility: hidden; pointer-events: none; transform: translate(0, 10px); }
	.mega-panel-inner { width: 100%; padding: 0 1.25rem; justify-content: flex-start; }
	.mega-item.open > .mega-panel { opacity: 1; visibility: visible; pointer-events: auto; transform: translate(0, 0); transition: opacity 160ms ease, transform 160ms ease; }
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

// Mega menu mobile toggle
document.querySelectorAll('.mega-item.has-panel > a').forEach(link => {
	link.addEventListener('click', (e) => {
		if (window.innerWidth <= 992) {
			e.preventDefault();
			const li = link.parentElement;
			li.classList.toggle('open');
		}
	});
});

// Position mega menu overlay relative to nav height
function updateMegaTop() {
	const anchor = document.querySelector('.mega-nav-wrapper');
	if (!anchor) return;
	const rect = anchor.getBoundingClientRect();
	const top = rect.bottom + window.scrollY;
	document.documentElement.style.setProperty('--mega-top', `${top}px`);
}

window.addEventListener('resize', updateMegaTop);
window.addEventListener('load', updateMegaTop);
updateMegaTop();
</script>
