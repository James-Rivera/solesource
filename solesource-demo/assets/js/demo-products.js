// Auto-generated demo product list for the static demo
(function(){
  const files = [
    "0803-nik37803810400w04y-1-webp-6962011c1e9c1.webp",
    "0803-nik55355816600w11h-1-webp-6961fa7bec2f4.webp",
    "0803-nikbq647218000w08h-1-webp-6961fbfd6eaf8.webp",
    "0803-nikdc077410100w07h-1-webp-6961fb15908b7.webp",
    "0803-nikfd2596101ivo07h-1-webp-6961fd35742cb.webp",
    "air-force-1.png",
    "airjordan-11-jpg-696200d30950b.jpg",
    "adidas-gazelle-indoor.jpg",
    "dunk-high-jpg-696206ac0e8d2.jpg",
    "gel-kayano-women-jpg-6961ed6b82ff6.jpg",
    "jordan-11-legend-blue.png",
    "af1-low-jpg-69620568acc37.jpg",
    "airmax90-jpg-6962090e3d97b.jpg",
    "blazer-low-jpg-69620798f34e2.jpg",
    "superstar-1-jpg-6968fabcbf796.jpg",
    "novablast-men-jpg-6961f491c8f5c.jpg",
    "pegasus-trail-jpg-6962165954ce1.jpg",
    "react-vision-jpg-69620abfaccec.jpg",
    "suede-classic-jpg-696364df6639f.jpg",
    "ultraboost-light-shoes-grey-ie17-png-6964218f273aa.png",
    "zoom-vomero5-jpg-69621a4078a7b.jpg",
    "tokuten-jpg-69621cbee4cfe.jpg",
    "mexico66-jpg-69621aa0224bd.jpg",
    "waffle-one-jpg-69620b7c6d582.jpg"
  ];

  function detectBrand(name){
    const n = name.toLowerCase();
    if(n.includes('nike') || n.includes('af1') || n.includes('air') || n.includes('pegasus') ) return 'Nike';
    if(n.includes('adidas') || n.includes('gazelle') || n.includes('superstar')) return 'Adidas';
    if(n.includes('jordan')) return 'Jordan';
    if(n.includes('asics') || n.includes('gel')) return 'Asics';
    return '';
  }

  window.demoProducts = files.map((f,i) => {
    const name = f.replace(/[-_]/g,' ').replace(/\.(jpg|png|webp|jpeg|gif)$/i,'');
    const brand = detectBrand(f);
    const price = Math.floor(3500 + Math.random() * 9000);
    return { id: 1000 + i, name: name.replace(/\d+/g,'').trim(), brand, image: `assets/img/products/${f}`, price };
  });
})();
