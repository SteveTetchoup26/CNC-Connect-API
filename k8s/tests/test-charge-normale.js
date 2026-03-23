import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const errorRate = new Rate('errors');
const responseTime = new Trend('response_time');

export const options = {
  stages: [
    { duration: '30s', target: 50 },  // montée à 50 users en 30s
    { duration: '1m',  target: 50 },  // maintien 50 users pendant 1min
    { duration: '30s', target: 0 },   // descente
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'],  // p95 < 2s
    errors: ['rate<0.01'],              // erreurs < 1%
  },
};

const BASE_URL = 'http://localhost:30080';

export default function () {

  sleep(1);

  // Test route API
  const res2 = http.get(`${BASE_URL}/api/v1/concours`, {
    headers: { 'Accept': 'application/json' },
  });
  check(res2, {
    'API répond': (r) => r.status !== 500,
    'temps < 2s': (r) => r.timings.duration < 2000,
  });
  errorRate.add(res2.status >= 500);
  responseTime.add(res2.timings.duration);

  sleep(1);
}
