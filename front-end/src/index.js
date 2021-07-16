import React from 'react';
import ReactDom from 'react-dom';
import { Provider } from 'react-redux';
import { BrowserRouter as Router } from 'react-router-dom';

import App from 'src/components/App';
import store from 'src/store';

import 'semantic-ui-css/semantic.min.css';

const rootReactElement = (
  <Router>
    <Provider store={store}>
      <App />
    </Provider>
  </Router>
);

const target = document.getElementById('root');

ReactDom.render(rootReactElement, target);
