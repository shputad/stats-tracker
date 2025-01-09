import puppeteer from 'puppeteer-extra';
import StealthPlugin from 'puppeteer-extra-plugin-stealth';
import { Solver } from '@2captcha/captcha-solver';
import { readFileSync } from 'fs';

puppeteer.use(StealthPlugin());

const solver = new Solver(process.argv[3]);

const scrapeData = async (page) => {
    await page.waitForSelector('.font-weight-medium');
    const content = await page.content();

    // Extract the desired data
    const regex = /(\d+)\s*логов/;
    const match = content.match(regex);

    return match ? match[1] : null;
};

const fetchCaptchaParams = async () => {
    const targetUrl = process.argv[2];

    if (!targetUrl) {
        console.error('Error: No URL provided.');
        process.exit(1);
    }

    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const preloadFile = readFileSync('resources/js/captcha/inject.js', 'utf8');
        await page.evaluateOnNewDocument(preloadFile);

        // Intercept console messages
        const paramsPromise = new Promise((resolve, reject) => {
            page.on('console', (msg) => {
                const txt = msg.text();
                if (txt.includes('intercepted-params:')) {
                    const params = JSON.parse(txt.replace('intercepted-params:', ''));
                    resolve(params);
                }
            });

            setTimeout(() => reject(new Error('Timeout waiting for CAPTCHA parameters.')), 10000);
        });

        await page.goto(targetUrl, { waitUntil: 'domcontentloaded' });

        const params = await paramsPromise;

        // Solve the CAPTCHA
        const res = await solver.cloudflareTurnstile(params);

        // Inject the token into the page
        await page.evaluate((token) => {
            cfCallback(token);
        }, res.data);

        // Wait for the page to load after solving the CAPTCHA
        await page.waitForNavigation({ waitUntil: 'networkidle2' });

        // Scrape the logs
        const logs = await scrapeData(page);

        // Return both the token and logs
        console.log(JSON.stringify({ token: res.data, logs }));
    } catch (error) {
        console.error('Error fetching CAPTCHA parameters:', error.message);
    } finally {
        await browser.close();
    }
};

fetchCaptchaParams();
