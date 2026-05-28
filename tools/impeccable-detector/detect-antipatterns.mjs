// Local patch for the impeccable CLI detector.
//
// The upstream `pbakaus/impeccable` skill ships `detect.mjs` as a thin loader
// for `scripts/detector/detect-antipatterns.mjs`, but the latter file is not
// included in the public repo. So out-of-the-box `node detect.mjs --json …`
// fails with "bundled detector not found".
//
// This module exports the same `detectCli` contract and implements a tight
// subset of the impeccable bans, anchored on the rules cited in the skill's
// shared design laws + brand/product reference docs. Source-of-truth lives
// here (tracked); install instructions in tools/impeccable-detector/README.md.

import fs from 'node:fs';
import path from 'node:path';

const RULES = [
  {
    id: 'em-dash',
    desc: 'Em-dashes (—) in copy. Shared design law: use commas, colons, semicolons, periods, or parentheses.',
    severity: 'warn',
    match: (txt) => {
      const out = [];
      txt.split('\n').forEach((line, i) => {
        if (line.includes('<!--')) return;
        if (line.includes('—')) out.push({ line: i + 1, snippet: line.trim().slice(0, 140) });
      });
      return out;
    },
  },
  {
    id: 'side-stripe-border',
    desc: 'Coloured side-stripe accent border (border-left/right ≥ 2px). Absolute ban.',
    severity: 'error',
    match: (txt) => {
      const re = /\bborder-(?:l|r)-[2-9]\b|border-(?:left|right)\s*:\s*[2-9]\s*px/g;
      return [...txt.matchAll(re)].map((m) => ({ snippet: m[0] }));
    },
  },
  {
    id: 'gradient-text',
    desc: 'Gradient text (background-clip: text + gradient). Absolute ban.',
    severity: 'error',
    match: (txt) => {
      const re = /\bbg-clip-text\b|background-clip\s*:\s*text/g;
      return [...txt.matchAll(re)].map((m) => ({ snippet: m[0] }));
    },
  },
  {
    id: 'glassmorphism',
    desc: 'backdrop-blur usage. Verify each is functional (glass over scrolling content / over photography), not decorative.',
    severity: 'info',
    match: (txt) => {
      const out = [];
      txt.split('\n').forEach((line, i) => {
        if (/backdrop-blur/.test(line)) out.push({ line: i + 1, snippet: line.trim().slice(0, 140) });
      });
      return out;
    },
  },
  {
    id: 'hero-metric-template',
    desc: '3+ adjacent big display numbers + small labels (SaaS hero-metric cliché). Absolute ban.',
    severity: 'error',
    match: (txt) => {
      // Three large display-weighted numbers within ~1.5kB of each other.
      const re = /font-display\s+font-(?:700|bold)\s+text-(?:3xl|4xl|5xl)[\s\S]{0,500}font-display\s+font-(?:700|bold)\s+text-(?:3xl|4xl|5xl)[\s\S]{0,500}font-display\s+font-(?:700|bold)\s+text-(?:3xl|4xl|5xl)/g;
      return [...txt.matchAll(re)].map(() => ({ snippet: 'three adjacent large display numbers' }));
    },
  },
  {
    id: 'kicker-repetition',
    desc: 'Repeated tiny uppercase tracked label "kicker" as section grammar. Brand register ban beyond 2 occurrences.',
    severity: 'warn',
    threshold: 3,
    match: (txt) => {
      const re = /uppercase\s+tracking-\[0\.[12]\d?em\]/g;
      const count = (txt.match(re) || []).length;
      return count >= 3 ? [{ snippet: `${count} occurrences (threshold 3)` }] : [];
    },
  },
  {
    id: 'identical-card-grid',
    desc: 'Same exact card class signature repeated ≥ 4 times in a flat grid (identical cards anti-pattern).',
    severity: 'warn',
    match: (txt) => {
      // Look for the same long class string repeated. Crude but effective.
      const re = /class="([^"]*\brounded-(?:2xl|3xl)\b[^"]*\bring-1\b[^"]*\bp-(?:5|6|7|8)\b[^"]*)"/g;
      const counts = new Map();
      let m;
      while ((m = re.exec(txt))) counts.set(m[1], (counts.get(m[1]) || 0) + 1);
      const hits = [];
      for (const [sig, n] of counts) {
        if (n >= 4) hits.push({ snippet: `${n}× «${sig.slice(0, 70)}…»` });
      }
      return hits;
    },
  },
];

const EXCLUDE_DIRS = new Set(['node_modules', '.git', '.agents', '.claude', '.impeccable', 'dist', 'build', '.next', '.cache']);
const INCLUDE_EXT = /\.(html?|astro|jsx|tsx|vue|svelte|mdx?)$/i;

function walk(target) {
  if (!fs.existsSync(target)) return [];
  const st = fs.statSync(target);
  if (st.isFile()) return [target];
  const out = [];
  for (const e of fs.readdirSync(target, { withFileTypes: true })) {
    if (e.isDirectory()) {
      if (EXCLUDE_DIRS.has(e.name) || e.name.startsWith('.')) continue;
      out.push(...walk(path.join(target, e.name)));
    } else if (INCLUDE_EXT.test(e.name)) {
      out.push(path.join(target, e.name));
    }
  }
  return out;
}

function scanFile(filePath) {
  const txt = fs.readFileSync(filePath, 'utf8');
  return RULES.flatMap((rule) => {
    const hits = rule.match(txt);
    return hits.length
      ? [{
          rule: rule.id,
          desc: rule.desc,
          severity: rule.severity || 'warn',
          file: filePath,
          count: hits.length,
          samples: hits.slice(0, 5),
        }]
      : [];
  });
}

export async function detectCli() {
  const argv = process.argv.slice(2);
  const json = argv.includes('--json');
  const fast = argv.includes('--fast');
  const targets = argv.filter((a) => !a.startsWith('--'));
  if (!targets.length) targets.push('.');

  const files = [...new Set(targets.flatMap(walk))];
  const findings = files.flatMap(scanFile);

  if (json) {
    process.stdout.write(JSON.stringify({
      scanned: files.length,
      findingCount: findings.length,
      findings,
      fast,
    }, null, 2) + '\n');
  } else {
    for (const f of findings) {
      const sev = f.severity.toUpperCase().padEnd(5, ' ');
      process.stdout.write(`${sev} ${f.rule.padEnd(24)} ${f.file}  ×${f.count}\n`);
      process.stdout.write(`        ${f.desc}\n`);
      for (const s of f.samples) {
        const where = s.line ? ` (L${s.line})` : '';
        process.stdout.write(`        · ${s.snippet}${where}\n`);
      }
    }
    process.stdout.write(`\nScanned ${files.length} file(s). ${findings.length} finding(s).\n`);
  }

  process.exit(findings.length ? 2 : 0);
}
