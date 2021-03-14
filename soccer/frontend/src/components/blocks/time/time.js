import {Utils} from '../../../utils';
import './time.css';

const Time = ({time, extra}) => {
    return(
        <div className='time'>{time}{Utils.makeSuperscript(extra, '+')}</div>
    );
}

export default Time;