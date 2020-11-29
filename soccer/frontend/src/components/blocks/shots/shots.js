import './shots.css';

const Shots = ({host, guest, half1, half2, last10, state, sg, sh}) => {
    const show = (sg || sg === 0) && (sh || sh === 0);
    const clsName = 'shots' 
        + (host ? ' h' : '') 
        + (guest ? ' g' : '') 
        + (half1 ? ' half1' : '') 
        + (half2 ? ' half2' : '') 
        + (last10 ? ' last10' : '')
        + (state === 1 && half1 || state == 3 && half2 ? ' bold' : '');

    return(
        <div  className= {clsName}>{ show ? `${sh}-${sg}` : '' }</div>
    )

}
export default Shots;