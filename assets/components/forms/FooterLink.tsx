import React from 'react'
import {Link} from "react-router-dom";

const FooterLink = ({text, linkText, href}: FooterLinkProps) => {
    return (
        <div className='text-center pt-4'>
            <p className="text-sm text-gray-500">
                {text}{" "}

                <Link to={href} className='footer-link'>
                    {linkText}
                </Link>
            </p>
        </div>
    )
}
export default FooterLink
