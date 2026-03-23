import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Counter } from 'k6/metrics';

const errorRate  = new Rate('errors');
const reqCounter = new Counter('total_requests');

export const options = {
  stages: [
    { duration: '30s', target: 100 },
    { duration: '5m',  target: 100 },
    { duration: '30s', target: 0   },
  ],
  thresholds: {
    http_req_duration: ['p(95)<3000'],
    errors: ['rate<0.02'],
  },
};

const BASE_URL = 'http://localhost:30080';

export default function () {
  const res = http.get(`${BASE_URL}/api/v1/concours`, {
    headers: { 'Accept': 'application/json' },
  });

  check(res, {
    'status ok': (r) => r.status < 500,
  });

  errorRate.add(res.status >= 500);
  reqCounter.add(1);
  sleep(1);
}
