# Perennial animated web widgets

Four animated marketing widgets, coded from the Figma motion designs in file
`OpeXLecEHRl0RU0WspDQQH`. Each one is a self-contained HTML snippet: no build step, no plugins and
no dependencies to install. Paste it into a page and it works.

| Widget | What it shows | Paste this file | Figma (desktop / mobile) |
|---|---|---|---|
| Scheduling | Weekly schedule board, job marked paid | `scheduling-widget/scheduling-widget-responsive.html` | [5:4](https://www.figma.com/design/OpeXLecEHRl0RU0WspDQQH/Untitled?node-id=5-4&m=dev) / [5:259](https://www.figma.com/design/OpeXLecEHRl0RU0WspDQQH/Untitled?node-id=5-259&m=dev) |
| Crew scheduling | Crew dispatch gantt, route map | `crew-widget/crew-widget-responsive.html` | [11:5](https://www.figma.com/design/OpeXLecEHRl0RU0WspDQQH/Untitled?node-id=11-5&m=dev) / [11:235](https://www.figma.com/design/OpeXLecEHRl0RU0WspDQQH/Untitled?node-id=11-235&m=dev) |
| Equipment insights | Asset inventory table, yield sparkline | `equipment-widget/equipment-widget-responsive.html` | [11:595](https://www.figma.com/design/OpeXLecEHRl0RU0WspDQQH/Untitled?node-id=11-595&m=dev) / [11:796](https://www.figma.com/design/OpeXLecEHRl0RU0WspDQQH/Untitled?node-id=11-796&m=dev) |
| Business overview | Control-centre dashboard, yield bars | `business-widget/business-widget-responsive.html` | [16:4](https://www.figma.com/design/OpeXLecEHRl0RU0WspDQQH/Untitled?node-id=16-4&m=dev) / [16:193](https://www.figma.com/design/OpeXLecEHRl0RU0WspDQQH/Untitled?node-id=16-193&m=dev) |

---

## Getting them live

### Elementor
1. Add an **HTML** widget where the graphic should go.
2. Paste the whole `*-responsive.html` file into it.
3. Set the containing section to full width with no horizontal padding.

### Gutenberg
1. Add a **Custom HTML** block.
2. Paste the whole file. Preview the page rather than judging it in the editor — the editor doesn't
   run the script, so the animation won't play there.

### Divi or another builder
Any "raw HTML" or "code" module works. The markup, CSS and JS are all in the one file.

### Or install the plugin, and use shortcodes
If you'd rather not paste HTML into pages, `dist/perennial-widgets.zip` is a small plugin that
registers a shortcode. Upload it under **Plugins → Add New → Upload Plugin**, activate, then:

```
[perennial_widget id="scheduling"]
[perennial_widget id="crew" loop="true" loop_delay="6000"]
```

Valid ids: `scheduling`, `crew`, `equipment`, `business`. The plugin enqueues the font itself
(disable with `add_filter( 'perennial_widgets_load_font', '__return_false' );`) and strips the
snippet's own font tags. Source is in `plugin/`; `build-all.sh` regenerates the zip.

### Things to watch for
- **Scripts must survive.** Some security plugins, and non-admin roles, strip `<script>` from post
  content. If a widget shows its finished state but never animates, that's the cause.
- **Sizing.** Desktop is a 1200px-wide design. At 1200px or wider it renders 1:1; in a narrower
  block it scales down proportionally. Below 768px of block width the mobile version takes over.
- **Fonts.** Each file loads Plus Jakarta Sans (weights 400–800) from Google Fonts. If the theme
  already loads that family, delete the three `<link>` tags at the top of the file to save a request.
- **No files to upload.** The crew widget's route map is embedded as a data URI (~41KB), so nothing
  needs to go into the Media Library.

---

## How they behave

- **Plays once**, when 35% of the widget (20% on mobile) scrolls into view. The 8-second timeline
  then holds its final frame.
- **Replay:** add `data-loop="true"` to the widget's root element (`.psw`, `.pcw`, `.pew` or `.pbo`
  for desktop; `.pswm`, `.pcwm`, `.pewm` or `.pbom` for mobile). `data-loop-delay="6000"` sets how
  long the final frame holds before replaying, in milliseconds.
- **Reduced motion:** visitors who have "reduce motion" enabled see the final frame with no
  animation. Same for anyone with JavaScript disabled.
- **Decorative by design:** the mock app panels carry `role="img"` and a text description, so screen
  readers get a one-line summary instead of reading out fake UI. Nothing is focusable or clickable.
- **Performance:** CSS animations only (opacity and transform), so they run on the compositor. No
  animation libraries. The only JavaScript is an IntersectionObserver to start playback and a
  ResizeObserver for desktop scaling.
- **Browsers:** current Chrome, Safari, Firefox and Edge. The desktop/mobile switch uses CSS
  container queries (Safari 16+, Chrome/Edge 105+, Firefox 110+). In an older browser both versions
  would show; if you need to support one, use the two separate files with a media query instead.

---

## Repo layout

```
<widget>/
  <widget>.html             desktop source
  <widget>-mobile.html      mobile source
  <widget>-responsive.html  GENERATED — this is the file to paste
  build.sh                  combines desktop + mobile into the responsive file
  assets/                   crew widget only: the route map image
preview.html                local preview with a timeline scrubber
demo.html                   GENERATED — all four widgets on one page
plugin/                     WordPress plugin source (shortcode wrapper)
dist/perennial-widgets.zip  GENERATED — installable plugin
build-all.sh                rebuilds every responsive file, demo.html and the plugin zip
```

Edit the desktop or mobile source, then run that widget's `./build.sh` — or `./build-all.sh` to
rebuild everything at once. Don't hand-edit a `*-responsive.html`, `demo.html` or the zip; they get
overwritten.

**Quickest look at the real thing:** open `demo.html` in a browser (it works straight from the
filesystem) and scroll. Each widget plays as it comes into view; narrow the window under 768px for
the mobile versions.

Class names are prefixed per widget (`psw-`, `pcw-`, `pew-`, `pbo-`, plus `m` for the mobile
variants) and every rule is scoped under the root class, so the widgets can't collide with theme
styles or with each other. Several widgets can sit on one page.

---

## Previewing, and comparing against Figma

```
python3 -m http.server 5178
```

Open <http://localhost:5178/preview.html> and pick the widget and version (desktop, mobile,
responsive). You can play, pause, scrub, slow playback to 0.1×, jump to any keyframe from the Figma
timeline, and force the width to 390 / 768 / 1024 / 1200.

To check motion against the design: in Figma open the frame in Dev Mode → **Open in timeline view**,
type a time into its current-time box, and type the same number into the preview's `ms` box. The two
frames should match. Playback speed differs slightly between Figma and the browser, so compare
frozen frames rather than watching both play.

---

## Fidelity notes

These were coded from the Figma layer data rather than by eye: layout, colors, type, easing curves
and keyframe times all come from the file, and every section measures within about 1–2px of Figma
(text line-height rounding accounts for the rest).

A few deliberate decisions, in case something looks like a bug:

- **No iOS status bar.** The mobile frames include a 9:41 / signal / battery bar. That's device
  chrome for the mockup, so it isn't rendered.
- **Figma quirks kept.** Where the design itself overlaps or truncates text — "Premium Mulch2 bags"
  in the business widget, the cut-off property names in its schedule table — the code matches it.
- **Scheduling widget, open slot.** Its Figma timeline dims one "+ Assign" slot to 35% at the end
  while the others stay fully visible. Faithful to the timeline, if slightly odd to look at.
- **Mobile icons.** In the scheduling and crew mobile frames, Figma renders the calendar and
  checkmark icons as solid lime shapes (an artifact of how the icons were imported). These use
  proper outline icons instead, matching those widgets' own desktop frames.
- **Forced line break.** The crew widget's desktop headline has a `<br>` so it wraps like Figma; the
  browser otherwise fits one more word on the first line.
- **Bar charts** grow from their centre, not up from the baseline, because that's what Figma's
  timeline does (checked by stopping it mid-growth).
