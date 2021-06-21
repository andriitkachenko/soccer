import { connect } from 'react-redux';

import { devtrace } from '../../../utils';

import './wait_indicator.css';

const WaitIndicator = ({ waiting }) => {
    devtrace("WaitIndicator");    
    
    if (!waiting) {
        return null;
    }
    return (
        <div className='wait-indicator'></div>
    );
}

const state2props = ({ waiting }) => {
    return { waiting };
}

export default connect(state2props)(WaitIndicator);