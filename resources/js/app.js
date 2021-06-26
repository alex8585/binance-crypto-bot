//import 'bootstrap/dist/css/bootstrap.min.css'
import ReactDOM from 'react-dom';
import React from 'react';
import Orders from './components/Orders/index.js';
import {compose, createStore, applyMiddleware} from 'redux';
import {rootReducer} from './redux/reducers/rootReducer';
import {Provider} from 'react-redux';
import thunk from 'redux-thunk';
import createSagaMiddleware from 'redux-saga';
import {sagaWatcher} from './redux/sagas';


const saga = createSagaMiddleware();

const composeSetup = process.env.NODE_ENV !== 'production' && typeof window === 'object' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ ?
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ : compose

const store = createStore(
    rootReducer,
    composeSetup(
        applyMiddleware(thunk, saga),
    )
);

saga.run(sagaWatcher);



require('./bootstrap');


const app = (
    <Provider store={store}>
        <Orders />
    </Provider>
)

if (document.querySelector('#orders')) {
    ReactDOM.render(
        app,
        document.querySelector('#orders')
    );
}
