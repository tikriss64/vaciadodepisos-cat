# vaciadodepisos.cat

<div align="center">

**🇬🇧 English · 🇫🇷 Français · 🇪🇸 Español · 󠁥󠁳󠁣󠁴󠁿 Català**

[![Live Website](https://img.shields.io/badge/Live%20Website-vaciadodepisos.cat-brightgreen)](https://vaciadodepisos.cat)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)
![SEO](https://img.shields.io/badge/SEO-Optimized-success)

</div>

---

## 🇬🇧 English

### Professional apartment clearance service website

Full production website for a professional apartment and property clearance service in Barcelona and surrounding areas. Built from scratch with a focus on SEO, performance, and local search ranking.

**Highlights:**
- Bilingual — Spanish (ES) and Catalan (CA), with multilingual thank-you pages (EN/FR/DE)
- 40+ SEO-optimized pages (blog articles, city pages, premium multilingual pages EN/FR/DE) targeting local search queries
- Service pages for each city: Barcelona, Hospitalet, Badalona, Sabadell, Terrassa, Sitges, Tarragona, Vilanova, Sant Pere Ribes...
- Portfolio section with real project photos
- Contact forms with PHP backend
- Core Web Vitals optimized (CLS fixes, image optimization)
- Fully responsive
- Sitemap, robots.txt, structured data, hreflang tags
- Google Business Profile integration

**Pages included:**
- Home (ES + CA)
- Services: apartment clearance, inheritance liquidation, hoarding syndrome, industrial premises, offices, urgent 24h service, elderly assistance...
- Blog with 30+ articles + city pages + premium multilingual pages (EN/FR/DE) = 70+ pages total
- Portfolio
- About us
- Legal pages (privacy policy, cookies, legal notice)

---

## 🇫🇷 Français

### Site web professionnel de vidage d'appartements

Site web de production complet pour un service professionnel de vidage d'appartements et de biens immobiliers à Barcelone et ses environs. Construit de zéro avec un focus sur le SEO, les performances et le référencement local.

**Points forts :**
- Bilingue — Espagnol (ES) et Catalan (CA), avec pages de remerciements multilingues (EN/FR/DE)
- 40+ pages optimisées SEO (articles, pages par ville, pages premium multilingues EN/FR/DE) ciblant des requêtes de recherche locales
- Pages de service pour chaque ville : Barcelone, Hospitalet, Badalona, Sabadell, Terrassa, Sitges, Tarragone, Vilanova...
- Section portfolio avec photos de vrais projets
- Formulaires de contact avec backend PHP
- Core Web Vitals optimisés
- Entièrement responsive

---

## 🇪🇸 Español

### Web profesional de vaciado de pisos

Sitio web de producción completo para un servicio profesional de vaciado de pisos y propiedades en Barcelona y alrededores. Construido desde cero con enfoque en SEO, rendimiento y posicionamiento local.

**Destacados:**
- Bilingüe — Español (ES) y Catalán (CA), con páginas de gracias multilingüe (EN/FR/DE)
- 40+ páginas SEO (artículos de blog, páginas por ciudad, páginas premium multilingüe EN/FR/DE)
- Páginas de servicio por ciudad: Barcelona, Hospitalet, Badalona, Sabadell, Terrassa, Sitges, Tarragona, Vilanova, Sant Pere Ribes...
- Sección portfolio con fotos de proyectos reales
- Formularios de contacto con backend PHP
- Core Web Vitals optimizados (correcciones CLS, optimización de imágenes)
- Totalmente responsive
- Sitemap, robots.txt, datos estructurados, etiquetas hreflang

---

## Tech Stack

| Technology | Usage |
|---|---|
| HTML5 / CSS3 | Structure and styling |
| Vanilla JavaScript | Interactivity, maps, form validation |
| PHP | Contact form backend |
| Google Maps API | Location map |
| Apache (.htaccess) | Redirects, URL rewriting, caching |
| XML Sitemap | SEO indexing |

## Structure

```
/
 index.html # Home (ES)
 index-ca.html # Home (CA)
 blog.html # Blog index (ES)
 blog-ca.html # Blog index (CA)
 portfolio.html # Portfolio (ES)
 portfolio-ca.html # Portfolio (CA)
 [city]-*.html # City service pages (ES + CA)
 [topic]-*.html # Blog articles (ES + CA)
 enviar.php # Contact form handler
 common.css # Shared styles
 home.css # Homepage styles
 script.js # JavaScript
 sitemap.xml # SEO sitemap
 robots.txt # Search engine directives
 imagenes/ # Images and assets
```

## Configuration

Before deploying, replace the placeholders:

```html
<!-- In all HTML files, replace: -->
key=YOUR_GOOGLE_MAPS_API_KEY
<!-- with your actual Google Maps API key -->
```

Configure `enviar.php` with your mail server settings for the contact form.

## Live Demo

**[https://vaciadodepisos.cat](https://vaciadodepisos.cat)**

## License

MIT — Code is open source. Content (texts, photos) are proprietary.
