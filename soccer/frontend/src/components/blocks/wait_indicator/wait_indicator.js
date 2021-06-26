import { connect } from 'react-redux';

import { devtrace } from '../../../utils';

import './wait_indicator.css';

const WaitIndicator = ({ waiting }) => {
    devtrace("WaitIndicator");    
    
    if (!waiting) {
        return null;
    }
    return (
//        <div className='wait-indicator'></div>
        <div className='wait-indicator-circle'>
            <svg id='wait'>
                <circle r="3" cx="4" cy="4"></circle>
            </svg>
        </div>
    );
}

const state2props = ({ waiting }) => {
    return { waiting };
}

export default connect(state2props)(WaitIndicator);