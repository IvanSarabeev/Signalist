import React, {FC} from 'react'
import {authLogout} from "@/app/api/auth";
import {useNavigate} from "react-router";
import {
    DropdownMenu,
    DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from "@/components/ui/dropdown-menu";
import {Button} from "@/components/ui/button";
import {Avatar, AvatarFallback, AvatarImage} from "@/components/ui/avatar";
import {LogOut} from "lucide-react";
import NavItems from "@/components/NavItems";

type UserDropdownProps = {
    user: User,
    initialStocks: StockWithWatchlistStatus[]
}

const UserDropdown: FC<UserDropdownProps> = ({user, initialStocks}) => {
    const navigate = useNavigate();

    const onSignOut = async () => {
        return await authLogout()
            .finally(() => {
                navigate('/sign-in');
            });
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant='ghost' className='flex items-center gap-3 text-gray-400 hover:text-yellow-500'>
                    <Avatar className='size-8'>
                        <AvatarImage src="https://avatars.githubusercontent.com/u/153423955?s=280&v=4" />
                        <AvatarFallback className='bg-yellow-500 text-yellow-900 text-sm font-bold'>
                            {/*{user.name[0]}*/}
                            Alibaba
                        </AvatarFallback>
                    </Avatar>
                    <div className="hidden md:flex flex-col items-start">
                        <span className="text-base font-medium text-gray-400">
                            {/*{user.name}*/}
                        </span>
                    </div>
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent className='bg-gray-400'>
                <DropdownMenuLabel>
                    <div className="flex relative items-center gap-3 py-2">
                        <Avatar className="size-10">
                            <AvatarImage src="https://avatars.githubusercontent.com/u/153423955?s=280&v=4" />
                            <AvatarFallback className="bg-yellow-500 text-yellow-900 text-sm font-bold">
                                {/*{user.name[0]}*/}
                                Alibaba
                            </AvatarFallback>
                        </Avatar>
                        <div className="flex flex-col">
                            <span className='text-base font-medium text-gray-400'>
                                {/*{user.name}*/}
                                John Johnson
                            </span>
                            <span className="text-sm text-gray-500">
                                {/*{user.email}*/}
                                john_johnson@gmail.com
                            </span>
                        </div>
                    </div>
                </DropdownMenuLabel>

                <DropdownMenuSeparator className='bg-gray-600' />

                <DropdownMenuItem onClick={onSignOut} className="text-gray-100 text-md font-medium focus:bg-transparent focus:text-yellow-500 transition-colors cursor-pointer">
                    <LogOut className="size-4 mr-2 hidden sm:block" />
                    Logout
                </DropdownMenuItem>

                <DropdownMenuSeparator className="hidden sm:block bg-gray-600" />

                <nav className="sm:hidden">
                    <NavItems initialStocks={initialStocks} />
                </nav>
            </DropdownMenuContent>
        </DropdownMenu>
    )
}

export default UserDropdown
