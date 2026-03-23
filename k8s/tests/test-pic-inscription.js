import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

const errorRate = new Rate('errors');

export const options = {
  stages: [
    { duration: '1m',  target: 50  },  // montée progressive
    { duration: '1m',  target: 100 },  // augmentation
    { duration: '1m',  target: 200 },  // pic maximal
    { duration: '30s', target: 0   },  // descente rapide
  ],
  thresholds: {
    http_req_duration: ['p(95)<3000'],
    errors: ['rate<0.05'],  // tolérance 5% pendant le pic
  },
};

const BASE_URL = 'http://localhost:30080';

export default function () {
  const res = http.get(`${BASE_URL}/api/v1/concours`, {
    headers: { 'Accept': 'application/json' },
  });

  check(res, {
    'pas d erreur serveur': (r) => r.status < 500,
  });

  errorRate.add(res.status >= 500);
  sleep(0.5);
}
