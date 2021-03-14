import React from 'react';
import {Utils} from '../../../utils';

import './start_time.css';

class StartTime extends React.Component {
    render() {
        return (
            <span className='start-time'>{Utils.getLocalTimeString(this.props.time)}</span>
        )
    }
}

export default StartTime;