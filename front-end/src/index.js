import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { BrowserRouter as Router } from 'react-router-dom';

// import './index.css';
// import './components/App/App.scss';

import App from './components/App/App';
import 'bootstrap/dist/css/bootstrap.min.css';
import store from './store';


const rootReactElement = (
  <Router>
    <Provider store={store}>
      <App />
    </Provider>
  </Router>
);

const target = document.getElementById('root');

ReactDOM.render(rootReactElement, target);

