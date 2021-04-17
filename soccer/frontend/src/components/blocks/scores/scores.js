import { useRef } from 'react';
import classNames from 'classnames';

import './scores.css';

const Scores = ({host, guest, all, h1, h2}) => {
    const prevScore = useRef(null);

    const isAll = all || all === 0;
    const isHalf1 = h1 || h1 === 0;
    const isHalf2 = h2 || h2 === 0;

    const score = isAll ? all : (isHalf1 ? h1 : h2);

    const isChanged = prevScore.current !== null && prevScore.current < score;
    prevScore.current = score;
    
    const clsName = classNames('scores', {
        h : host,
        g : guest,
        all : isAll,
        half1 : isHalf1,
        half2 : isHalf2,
        highlight : (isHalf1 || isHalf2) && isChanged
    });

    return (
        <div className={ clsName }>{ score }</div>
    );
}

export default Scores;