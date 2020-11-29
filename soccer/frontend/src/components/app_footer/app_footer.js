import './app_footer.css';

const AppFooter = ({count}) => {
    var today = new Date();
    var date = `${today.getDate()}-${today.getMonth()+1}-${today.getFullYear()}`;

    if (count) {
        return (
            <div className='app-footer'>
                <div className='count'>{count} games</div>
                <div className='date'>{date}</div>
            </div>
        );
    }
    return (
        <div className='app-footer'>
            <div className='date'>{date}</div>
        </div>
    );

}

export default AppFooter;
