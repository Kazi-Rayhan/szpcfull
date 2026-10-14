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
      box-sizing: border-box;
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
    .term {
      position: fixed;
      left: 1.5rem;
      bottom: 1.5rem;
      z-index: 4;
      width: 22rem;
      overflow: hidden;
      border-radius: 10px;
      background: rgba(10, 12, 16, 0.86);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 18px 50px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(0, 255, 65, 0.06);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-size: 0.72rem;
      line-height: 1.65;
      color: #d1d5db;
      animation: term-in 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) 0.4s both;
    }
    .term-bar {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.55rem 0.75rem;
      background: rgba(255, 255, 255, 0.05);
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    }
    .term-bar i { width: 0.6rem; height: 0.6rem; border-radius: 50%; background: #ff5f57; }
    .term-bar i:nth-child(2) { background: #febc2e; }
    .term-bar i:nth-child(3) { background: #28c840; }
    .term-title { flex: 1; text-align: center; margin-right: 1.8rem; font-size: 0.65rem; color: #9ca3af; letter-spacing: 0.04em; }
    .term-body {
      height: 10.5rem;
      padding: 0.7rem 0.9rem;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      overflow: hidden;
    }
    .term-line { white-space: pre-wrap; word-break: break-word; }
    .term-prompt { color: #00FF41; }
    .term-path { color: #60a5fa; }
    .term-dim { color: #6b7280; }
    .term-ok { color: #00FF41; }
    .term-hl { color: #f3f4f6; font-weight: 700; }
    .term-cursor {
      display: inline-block;
      width: 0.5em;
      height: 1.1em;
      margin-left: 1px;
      vertical-align: text-bottom;
      background: #00FF41;
      animation: term-blink 1s steps(1) infinite;
    }
    .term-foot {
      display: flex;
      justify-content: space-between;
      padding: 0.45rem 0.9rem;
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      font-size: 0.6rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #6b7280;
    }
    .term-foot b { color: #00FF41; font-weight: 400; }
    @keyframes term-in { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
    @keyframes term-blink { 50% { opacity: 0; } }
    @media (max-height: 720px) {
      .term-body { height: 6.6rem; }
    }
    @media (max-width: 640px) {
      .copy { padding-bottom: 10.5rem; }
      .term { left: 1rem; right: 1rem; bottom: 1rem; width: auto; font-size: 0.64rem; line-height: 1.55; }
      .term-bar { padding: 0.4rem 0.7rem; }
      .term-body { height: 4.6rem; padding: 0.5rem 0.8rem; }
      .term-foot { padding: 0.35rem 0.8rem; font-size: 0.55rem; }
    }
    @media (prefers-reduced-motion: reduce) {
      .slide { transition: none; transform: none; }
      .term { animation: none; }
      .term-cursor { animation: none; }
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
    <aside class="term" data-testid="boot-terminal" aria-label="University of Global Village, Department of CSE">
      <div class="term-bar" aria-hidden="true">
        <i></i><i></i><i></i>
        <span class="term-title">ugv@cse: ~/szpc</span>
      </div>
      <div class="term-body" aria-hidden="true"></div>
      <div class="term-foot">
        <span>University of Global Village</span>
        <b>Dept. of CSE</b>
      </div>
    </aside>
  </main>
  <script>
    (() => {
      const body = document.querySelector('.term-body');
      const prompt = '<span class="term-prompt">ugv@cse</span>:<span class="term-path">~/szpc</span>$ ';
      const script = [
        { cmd: 'whoami', out: ['<span class="term-hl">University of Global Village</span>, Barishal'] },
        { cmd: 'cat dept.txt', out: ['Department of CSE · UGV Programming Club'] },
        { cmd: 'ls editions/', out: ['szpc-1/  szpc-2/  szpc-3/  <span class="term-ok">szpc-4/</span>'] },
        { cmd: './szpc --edition=4 --boot', progress: true, out: ['<span class="term-ok">✓</span> <span class="term-hl">4th SZPC</span> — coming soon'] },
      ];
      const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
      const cursor = () => {
        const el = document.createElement('span');
        el.className = 'term-cursor';
        return el;
      };
      const addLine = (html = '') => {
        const line = document.createElement('div');
        line.className = 'term-line';
        line.innerHTML = html;
        body.appendChild(line);
        while (body.children.length > 9) body.firstElementChild.remove();
        return line;
      };
      const bar = (pct) => {
        const filled = Math.round(pct / 5);
        return `<span class="term-dim">[</span><span class="term-ok">${'#'.repeat(filled)}</span><span class="term-dim">${'.'.repeat(20 - filled)}]</span> ${String(pct).padStart(3)}%`;
      };

      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        script.forEach(({ cmd, progress, out }) => {
          addLine(prompt + cmd);
          if (progress) addLine(bar(100));
          out.forEach((html) => addLine(html));
        });
        addLine(prompt).appendChild(cursor());
        return;
      }

      const run = async () => {
        await sleep(900);
        while (true) {
          body.innerHTML = '';
          for (const { cmd, progress, out } of script) {
            const line = addLine(prompt);
            const typed = document.createElement('span');
            line.append(typed, cursor());
            await sleep(500);
            for (const char of cmd) {
              typed.textContent += char;
              await sleep(45 + Math.random() * 75);
            }
            await sleep(280);
            line.lastChild.remove();
            if (progress) {
              const bootLine = addLine(bar(0));
              for (let pct = 0; pct < 100;) {
                pct = Math.min(100, pct + Math.ceil(Math.random() * 9));
                bootLine.innerHTML = bar(pct);
                await sleep(70);
              }
            }
            for (const html of out) {
              addLine(html);
              await sleep(160);
            }
          }
          addLine(prompt).appendChild(cursor());
          await sleep(6000);
        }
      };
      run();
    })();
  </script>
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
