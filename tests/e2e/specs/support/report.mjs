// Prints the audit rows as a Markdown table: module | option | status | error | screenshot.
import fs from 'node:fs';

const file = new URL('../../test-results/audit/rows.jsonl', import.meta.url);
const rows = fs.existsSync(file) ? fs.readFileSync(file, 'utf8').trim().split('\n').filter(Boolean).map((line) => JSON.parse(line)) : [];
const cell = (text) => String(text ?? '').replace(/\|/g, '\\|').replace(/\s+/g, ' ').slice(0, 300);
const count = (status) => rows.filter((row) => row.status === status).length;

console.log(`PASS ${count('PASS')} · FAIL ${count('FAIL')} · NOT BUILT ${count('NOT BUILT')} · log notes ${count('NOTE')}\n`);
console.log('| Module | Option | Status | Error | Screenshot |\n|---|---|---|---|---|');
for (const row of rows) console.log(`| ${cell(row.module)} | ${cell(row.option)} | ${row.status} | ${cell(row.error)} | ${row.screenshot ? `\`${row.screenshot}\`` : ''} |`);
