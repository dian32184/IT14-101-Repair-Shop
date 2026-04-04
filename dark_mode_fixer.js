const fs = require('fs');
const path = require('path');

const walkSync = function (dir, filelist) {
    let files = fs.readdirSync(dir);
    filelist = filelist || [];
    files.forEach(function (file) {
        if (fs.statSync(dir + '/' + file).isDirectory()) {
            filelist = walkSync(dir + '/' + file, filelist);
        }
        else {
            if (file.endsWith('.blade.php')) {
                filelist.push(dir + '/' + file);
            }
        }
    });
    return filelist;
};

// All Blade files in resources/views
const files = walkSync('./resources/views');

const rules = [
    { match: /\bbg-white\b/g, replace: 'bg-white dark:bg-slate-800' },
    { match: /\bbg-gray-50\b/g, replace: 'bg-gray-50 dark:bg-slate-700/50' },
    { match: /\bbg-gray-100\b/g, replace: 'bg-gray-100 dark:bg-slate-700' },
    { match: /\btext-gray-900\b/g, replace: 'text-gray-900 dark:text-white' },
    { match: /\btext-gray-800\b/g, replace: 'text-gray-800 dark:text-slate-100' },
    { match: /\btext-gray-700\b/g, replace: 'text-gray-700 dark:text-slate-200' },
    { match: /\btext-gray-600\b/g, replace: 'text-gray-600 dark:text-slate-300' },
    { match: /\btext-gray-500\b/g, replace: 'text-gray-500 dark:text-slate-400' },
    { match: /\bborder-gray-100\b/g, replace: 'border-gray-100 dark:border-slate-700' },
    { match: /\bborder-gray-200\b/g, replace: 'border-gray-200 dark:border-slate-600' },
    { match: /\bborder-gray-300\b/g, replace: 'border-gray-300 dark:border-slate-500' },
    { match: /\bhover:bg-gray-50\b/g, replace: 'hover:bg-gray-50 dark:hover:bg-slate-700/50' },
    { match: /\bhover:bg-gray-100\b/g, replace: 'hover:bg-gray-100 dark:hover:bg-slate-600' }
];

let updatedCount = 0;

files.forEach(file => {
    // skip layouts to avoid duplicating the dark mode classes recently added or breaking app.blade.php
    if (file.includes('layouts') || file.includes('dashboard.blade.php')) return;

    let content = fs.readFileSync(file, 'utf8');
    let original = content;

    rules.forEach(rule => {
        // Only replace if it doesn't already have dark mode prefix nearby
        content = content.replace(rule.match, (matched, index, fullString) => {
            // Check if the dark variant is already present right after the match
            const nextChars = fullString.substring(index, index + 50);
            if (nextChars.includes('dark:')) return matched;

            return rule.replace;
        });
    });

    if (content !== original) {
        fs.writeFileSync(file, content, 'utf8');
        updatedCount++;
    }
});

console.log(`Updated ${updatedCount} blade files with dark mode properties.`);
