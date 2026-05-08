import axios from 'axios';
import Conveyor from 'socket-conveyor-client';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Conveyor = Conveyor;
