<!doctype html>
<html lang="en">
<head>
  @include('partials.site-head', [
    'seoPage' => $seoPage ?? 'home',
    'seoTitle' => $seoTitle ?? "4th SZPC — Coming soon",
    'seoDescription' => 'The 4th South Zone Programming Contest is coming soon.',
  ])
  <style>
    html, body { margin: 0; height: 100%; background: #1a1d24; color: #f3f4f6; font-family: Inter, system-ui, sans-serif; }
    .stage { position: relative; min-height: 100vh; overflow: hidden; }
    .slide {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      opacity: 0;
      transform: scale(1.06);
      transition: opacity 1.1s ease, transform 7s ease;
    }
    .slide.is-active { opacity: 1; transform: scale(1); z-index: 1; }
    .shade {
      position: absolute;
      inset: 0;
      z-index: 2;
      background: linear-gradient(to top, rgba(26, 29, 36, 0.82), rgba(26, 29, 36, 0.35) 45%, rgba(26, 29, 36, 0.45));
    }
    .copy {
      position: relative;
      z-index: 3;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 2rem;
    }
    .mark {
      width: 3rem;
      height: 3rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid #00FF41;
      color: #00FF41;
      font-family: "Plus Jakarta Sans", Inter, sans-serif;
      font-weight: 800;
    }
    h1 {
      margin: 0;
      font-family: "Plus Jakarta Sans", Inter, sans-serif;
      font-weight: 800;
      font-size: clamp(3rem, 10vw, 7.5rem);
      letter-spacing: -0.04em;
      line-height: 0.95;
    }
    h1 span { color: #00FF41; }
    p {
      margin: 1.25rem 0 0;
      font-size: clamp(1rem, 2.4vw, 1.35rem);
      letter-spacing: 0.42em;
      text-transform: uppercase;
      color: #d1d5db;
    }
    @media (prefers-reduced-motion: reduce) {
      .slide { transition: none; transform: none; }
    }
  </style>
</head>
<body>
  <main class="stage" data-testid="coming-soon">
    @foreach ($images as $image)
      <div class="slide @if ($loop->first) is-active @endif" style="background-image: url('{{ $image }}')" role="img" aria-label="SZPC gallery photo {{ $loop->iteration }}"></div>
    @endforeach
    <div class="shade" aria-hidden="true"></div>
    <div class="copy">
      <div class="mark" aria-hidden="true">SZ</div>
      <h1>4th <span>SZPC</span></h1>
      <p>Coming soon</p>
    </div>
  </main>
  @if (count($images) > 1)
    <script>
      const slides = Array.from(document.querySelectorAll('.slide'));
      let index = 0;
      const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (!reduceMotion) {
        setInterval(() => {
          slides[index].classList.remove('is-active');
          index = (index + 1) % slides.length;
          slides[index].classList.add('is-active');
        }, 5000);
      }
    </script>
  @endif
</body>
</html>
