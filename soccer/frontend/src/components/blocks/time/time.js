import { useRef } from 'react';
import {Utils} from '../../../utils';
import classNames from 'classnames';

import './time.css';

const Time = ({time, extra, track}) => {
    const prevTime = useRef(null);
    let changed = false;
    // TODO track time by last update time
    if (false && track) {
        changed = prevTime.current && prevTime.current < time;
        prevTime.current = time;
    }
    const clsNames = classNames('time', { highlight : changed });

    return(
        <div className={clsNames}>
            {time}{Utils.makeSuperscript(extra, '+')}
        </div>
    );
}

export default Time;