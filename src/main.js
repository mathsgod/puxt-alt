import Vue from 'vue'

import Element from 'element-ui';
Vue.use(Element, {
  size: "small",
});


/* import App from './App.vue'

Vue.config.productionTip = false

new Vue({
  render: h => h(App),
}).$mount('#app')
 */

import Card from "./Card.vue";
import CardBody from "./CardBody.vue";
import CardHeader from "./CardHeader.vue";
import CardFooter from "./CardFooter.vue";

Vue.component("card", Card);
Vue.component("card-body", CardBody);
Vue.component("card-header", CardHeader);
Vue.component("card-footer", CardFooter);


import RTable from "./r-table";
import RTableColumn from "./r-table-column";
import RTableDropdownItem from "./r-table-dropdown-item";
Vue.component("r-table", RTable);
Vue.component("r-table-column", RTableColumn);
Vue.component("r-table-dropdown-item", RTableDropdownItem);


Vue.component("vue", () => import("./vue"));



let init_vue = function (element) {
  var nodes = element.querySelectorAll("r-table, card");
  nodes.forEach(node => {
    new Vue({
      el: node
    });
  });
}
document.addEventListener("DOMContentLoaded", () => {
  console.log("content loaded");

  let observer = new MutationObserver(mutationList => {
    mutationList.forEach(record => {
      init_vue(record.target);
    });
  });
  observer.observe(document.body, { attributes: false, childList: true, subtree: true });
  init_vue(document);

  
});
