import Vue from 'vue'

// axios
import axios from 'axios'

let baseUrl = "http://143.198.208.110:8113/api";
if (location.hostname === "localhost" || location.hostname === "127.0.0.1"){
  baseUrl = "http://localhost:8113/api/";
}

const axiosIns = axios.create({
  // You can add your headers here
  // ================================
  baseURL: baseUrl,
  // baseURL: 'http://localhost:8105/api/',
  // timeout: 1000,
  // headers: {'X-Custom-Header': 'foobar'}
})

Vue.prototype.$http = axiosIns

export default axiosIns
