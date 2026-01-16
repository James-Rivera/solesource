<?php
include __DIR__ . '/../products/products.php';
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
$navData = [
	'Men' => ['brands' => [], 'sports' => []],
	'Women' => ['brands' => [], 'sports' => []],
];

foreach (array_keys($navData) as $genderKey) {
	$brandStmt = $conn->prepare("SELECT DISTINCT brand FROM products WHERE status='active' AND brand <> '' AND (gender = ? OR secondary_gender = ?) ORDER BY brand ASC");
	$brandStmt->bind_param('ss', $genderKey, $genderKey);
	$brandStmt->execute();
	$brandRes = $brandStmt->get_result();
	while ($row = $brandRes->fetch_assoc()) {
		$navData[$genderKey]['brands'][] = $row['brand'];
	}
	$brandStmt->close();

	$sportStmt = $conn->prepare("SELECT DISTINCT sport FROM products WHERE status='active' AND sport IS NOT NULL AND sport <> '' AND (gender = ? OR secondary_gender = ?) ORDER BY sport ASC");
	$sportStmt->bind_param('ss', $genderKey, $genderKey);
	$sportStmt->execute();
	$sportRes = $sportStmt->get_result();
	while ($row = $sportRes->fetch_assoc()) {
		$navData[$genderKey]['sports'][] = $row['sport'];
	}
	$sportStmt->close();
}

$navGenders = array_keys($navData);
$navBrands = array_values(array_unique(array_merge(...array_map(fn($g) => $navData[$g]['brands'], $navGenders))));
$navSports = array_values(array_unique(array_merge(...array_map(fn($g) => $navData[$g]['sports'], $navGenders))));

if (empty($navBrands)) { $navBrands = ['Nike','Adidas','Asics','Puma']; }
?>
<header>
	<nav class="navbar navbar-expand-lg navbar-dark bg-brand-black border-bottom border-brand-dark-gray">
		<div class="container-xxl align-items-center">
			<div class="d-flex align-items-center w-100 d-lg-none mobile-header-bar">
				<a class="navbar-brand mb-0" href="index.php">
					<img src="<?php echo asset('/assets/svg/logo-big-white.svg'); ?>" alt="SoleSource Logo" height="28">
				</a>
				<button class="btn text-white mobile-search-btn" type="button" aria-label="Search">
					<i class="bi bi-search fs-5"></i>
				</button>
				<div class="d-flex align-items-center gap-2 ms-auto">
					<?php if ($isCartFlow): ?>
						<a class="btn text-white mobile-cart-btn position-relative" href="cart.php" aria-label="Cart">
							<i class="bi bi-cart3 fs-5"></i>
							<span id="header-cart-count" class="position-absolute badge rounded-pill bg-danger header-cart-badge <?php echo $cartCount === 0 ? 'd-none' : ''; ?>"><?php echo $cartCount; ?></span>
						</a>
					<?php else: ?>
						<button class="btn text-white mobile-cart-btn position-relative cart-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" aria-controls="cartDrawer" aria-label="Open cart">
							<i class="bi bi-cart3 fs-5"></i>
							<span id="header-cart-count" class="position-absolute badge rounded-pill bg-danger header-cart-badge <?php echo $cartCount === 0 ? 'd-none' : ''; ?>"><?php echo $cartCount; ?></span>
						</button>
					<?php endif; ?>
					<button class="btn text-white p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#navDrawer" aria-controls="navDrawer" aria-label="Open menu">
						<i class="bi bi-list fs-4"></i>
					</button>
				</div>
			</div>

			<a class="navbar-brand d-none d-lg-flex align-items-center" href="index.php">
				<img src="<?php echo asset('/assets/svg/logo-big-white.svg'); ?>" alt="SoleSource Logo" height="30">
			</a>
			<button class="navbar-toggler border-0 d-none" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"
				aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="topNav">
				<div class="d-lg-flex align-items-center w-100 gap-3 gap-lg-4">
					<div class="search-container position-relative flex-grow-1 my-lg-0 order-lg-2">
						<input id="globalSearchInput" class="form-control search-pill" type="search" placeholder="Search" aria-label="Search">
						<div id="globalSearchResults" class="global-search-dropdown d-none"></div>
					</div>

					<ul class="navbar-nav align-items-center gap-lg-3 order-lg-3 flex-row flex-lg-row gap-3 mb-2 mb-lg-0 ms-lg-3 small d-none d-lg-flex">
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

					<div class="d-flex align-items-center gap-2 order-lg-4 flex-wrap ms-lg-3 d-none d-lg-flex">
						<?php if (!$isLoggedIn): ?>
							<a class="btn btn-login" href="login.php">Log In</a>
							<a class="btn btn-signup" href="signup.php">Signup</a>
						<?php else: ?>
							<div class="dropdown">
								<button class="btn account-btn text-white d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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

	<!-- Mobile full-screen nav drawer -->
	<div class="offcanvas offcanvas-end nav-drawer" tabindex="-1" id="navDrawer" aria-labelledby="navDrawerLabel" data-bs-backdrop="true">
		<div class="offcanvas-header">
			<h5 class="offcanvas-title text-white" id="navDrawerLabel">Browse</h5>
			<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body d-flex flex-column gap-3">
			<div class="mobile-nav-group">
				<div class="mobile-nav-heading">Shop</div>
				<?php foreach ($navGenders as $gender): ?>
					<a class="mobile-nav-link" href="shop.php?gender=<?php echo urlencode($gender); ?>"><?php echo htmlspecialchars($gender); ?></a>
				<?php endforeach; ?>
				<a class="mobile-nav-link" href="shop.php">All Products</a>
			</div>

			<?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
			<div class="mobile-nav-group">
				<div class="mobile-nav-heading">Admin</div>
				<a class="mobile-nav-link" href="admin/index.php">
					Admin Panel
				</a>
			</div>
			<?php endif; ?>

			<?php if (!empty($navBrands)): ?>
			<div class="mobile-nav-group">
				<div class="mobile-nav-heading">Top Brands</div>
				<div class="mobile-nav-pills py-2">
					<?php foreach (array_slice($navBrands, 0, 12) as $brand): ?>
						<a class="mobile-nav-pill" href="shop.php?brand=<?php echo urlencode($brand); ?>"><?php echo htmlspecialchars($brand); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if (!empty($navSports)): ?>
			<div class="mobile-nav-group">
				<div class="mobile-nav-heading">Sport</div>
				<div class="mobile-nav-pills">
					<?php foreach (array_slice($navSports, 0, 12) as $sport): ?>
						<a class="mobile-nav-pill" href="shop.php?sport=<?php echo urlencode($sport); ?>"><?php echo htmlspecialchars($sport); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="mobile-nav-group">
				<div class="mobile-nav-heading">Account</div>
				<?php if (!$isLoggedIn): ?>
					<a class="mobile-nav-link" href="login.php">Log In</a>
					<a class="mobile-nav-link" href="signup.php">Sign Up</a>
				<?php else: ?>
					<a class="mobile-nav-link" href="profile.php">Profile</a>
					<a class="mobile-nav-link" href="profile.php?tab=orders">Orders</a>
					<a class="mobile-nav-link" href="logout.php">Log out</a>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<nav class="bg-brand-black border-bottom border-brand-dark-gray py-1 mega-nav-wrapper">
		<div class="container-xxl">
			<ul class="mega-nav">
				<?php foreach ($navGenders as $gender): ?>
				<?php $genderBrands = $navData[$gender]['brands'] ?? []; $genderSports = $navData[$gender]['sports'] ?? []; ?>
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
							<?php if (!empty($genderBrands)): ?>
							<div class="mega-col">
								<div class="mega-title">Brands</div>
								<?php foreach ($genderBrands as $brand): ?>
									<a href="shop.php?gender=<?php echo urlencode($gender); ?>&brand=<?php echo urlencode($brand); ?>"><?php echo htmlspecialchars($brand); ?></a>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
							<?php if (!empty($genderSports)): ?>
							<div class="mega-col">
								<div class="mega-title">Sport</div>
								<?php foreach ($genderSports as $sport): ?>
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


<script>
const input = document.getElementById('globalSearchInput');
const dropdown = document.getElementById('globalSearchResults');
const mobileSearchBtn = document.querySelector('.mobile-search-btn');
const topNav = document.getElementById('topNav');

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
		const res = await fetch(`/includes/search/search.php?q=${encodeURIComponent(term)}`, { signal: searchController.signal });
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

mobileSearchBtn?.addEventListener('click', () => {
	document.body.classList.toggle('search-open');
	if (window.bootstrap && topNav && window.innerWidth <= 992) {
		const bsCollapse = bootstrap.Collapse.getOrCreateInstance(topNav, { toggle: false });
		bsCollapse.show();
	}
	setTimeout(() => input?.focus(), 120);
});

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

// Close nav drawer on outside click (offcanvas handles backdrop, this is a safety for clicks on document)
document.addEventListener('click', (e) => {
	const drawer = document.getElementById('navDrawer');
	if (!drawer) return;
	const isOpen = drawer.classList.contains('show');
	if (!isOpen) return;
	const trigger = e.target.closest('[data-bs-target="#navDrawer"]');
	if (trigger) return;
	if (!drawer.contains(e.target)) {
		const off = bootstrap.Offcanvas.getInstance(drawer);
		off?.hide();
	}
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
